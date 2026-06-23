<?php
	$myAccess = checkUserAccess();
?>
<!-- BEGIN CONTAINER -->
<div class="page-container">
	<!-- BEGIN SIDEBAR -->
	<div class="page-sidebar-wrapper">
		<!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
		<!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
		<div class="page-sidebar navbar-collapse collapse">
			<!-- BEGIN SIDEBAR MENU -->
			<!-- DOC: Apply "page-sidebar-menu-light" class right after "page-sidebar-menu" to enable light sidebar menu style(without borders) -->
			<!-- DOC: Apply "page-sidebar-menu-hover-submenu" class right after "page-sidebar-menu" to enable hoverable(hover vs accordion) sub menu mode -->
			<!-- DOC: Apply "page-sidebar-menu-closed" class right after "page-sidebar-menu" to collapse("page-sidebar-closed" class must be applied to the body element) the sidebar sub menu mode -->
			<!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
			<!-- DOC: Set data-keep-expand="true" to keep the submenues expanded -->
			<!-- DOC: Set data-auto-speed="200" to adjust the sub menu slide up/down speed -->
            <?php
            if($this->session->userdata('role')=='Admin'):
                ?>
                <ul class="page-sidebar-menu " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">

                    <!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
                    <li class="sidebar-toggler-wrapper">
                        <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
                        <div class="sidebar-toggler">
                        </div>
                        <!-- END SIDEBAR TOGGLER BUTTON -->
                    </li>
                    <!-- DOC: To remove the search box from the sidebar you just need to completely remove the below "sidebar-search-wrapper" LI element -->
                    <li class="sidebar-search-wrapper">
                        <!-- BEGIN RESPONSIVE QUICK SEARCH FORM -->
                        <!-- DOC: Apply "sidebar-search-bordered" class the below search form to have bordered search box -->
                        <!-- DOC: Apply "sidebar-search-bordered sidebar-search-solid" class the below search form to have bordered & solid search box -->
                        <!--<form class="sidebar-search " action="extra_search.html" method="POST">
                            <a href="javascript:;" class="remove">
                            <i class="icon-close"></i>
                            </a>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search...">
                                <span class="input-group-btn">
                                <a href="javascript:;" class="btn submit"><i class="icon-magnifier"></i></a>
                                </span>
                            </div>
                        </form>-->
                        <!-- END RESPONSIVE QUICK SEARCH FORM -->
                        <br />
                    </li>
                    <li class="start <?php if($this->uri->segment(1)=='dashboard'){echo 'active';}?>">
                        <a href="<?php echo base_url()?>">
                            <i class="icon-home"></i>
                            <span class="title">
					Dashboard</span>
                            <span class="selected"></span>
                        </a>
                    </li>
                    <?php
                    //$total_new_online_applications = $this->db->get_where('apply_now', array('status'=>0))->result_array();
                    //$total_clear_online_applications = $this->db->get_where('apply_now', array('status'=>1))->result_array();
                    ?>
                    <li class="<?php if($this->uri->segment(1)=='online_application'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-comment"></i>
                            <span class="title">Online Application</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='online_application'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='new_applications'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/online_application/new_applications">
                                    <i class="fa fa-envelope"></i>
                                    <span class="badge badge-danger"><?php echo newApplicationsCount();?></span>New Applications</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='pending_applications'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/online_application/pending_applications">
                                    <i class="fa fa-envelope"></i>
                                    <span class="badge badge-danger"><?php echo pendingApplicationsCount();?></span>Pending Applications</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='checked_applications'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/online_application/checked_applications">
                                    <i class="fa fa-check"></i>
                                    Checked Applications</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_applications'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/online_application/all_applications">
                                    <i class="fa fa-list"></i>
                                    All Applications</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='confirmed_admissions'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/online_application/confirmed_admissions">
                                    <i class="fa fa-list"></i>
                                    Confirmed Admissions</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='dynamic_forms'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/online_application/dynamic_forms">
                                    <i class="fa fa-wpforms"></i>
                                    Dynamic Forms</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='upload_fb_leads'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/online_application/upload_fb_leads">
                                    <i class="fa fa-upload"></i>
                                    Upload Facebook Leads</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='access'){echo 'active';}?>">
                        <a href="<?php echo site_url()?>/access">
                            <i class="fa fa-list"></i>
                            <span class="title">
                            Access </span>
                            <span class="selected"></span>
                        </a>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='human_resource'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-comment"></i>
                            <span class="title">Human resourcs</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='human_resource'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">

                            <li class="<?php if($this->uri->segment(1)=='myattendence'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/locations/check">
                                    <i class="fa fa-map-marker"></i>
                                    <span class="title">
                            Locations </span>
                                    <span class="selected"></span>
                                </a>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='hr'){echo 'active';}?>">
                                <a href="javascript:;">
                                    <i class="fa fa-users"></i>
                                    <span class="title">HR</span>
                                    <span class="selected"></span>
                                    <span class="arrow <?php if($this->uri->segment(1)=='hr'){echo 'open';}?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="<?php if($this->uri->segment(2)=='add_interview'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/hr/add_interview">
                                            <i class="icon-plus"></i>
                                            Add Interview</a>
                                    </li>
                                    <li class="<?php if($this->uri->segment(2)=='subjects'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/hr/all_interviews">
                                            <i class="fa fa-list"></i>
                                            All Interview</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='attendence'){echo 'active';}?>">
                                <a href="javascript:;">
                                    <i class="fa fa-users"></i>
                                    <span class="title">Attendence</span>
                                    <span class="selected"></span>
                                    <span class="arrow <?php if($this->uri->segment(1)=='attendence'){echo 'open';}?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="<?php if($this->uri->segment(2)=='add_attendence'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/attendence/add_attendence">
                                            <i class="fa fa-envelope"></i>
                                            Add Attendence</a>
                                    </li>
                                    <li class="<?php if($this->uri->segment(2)=='add_attendence'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/excel_import/index">
                                            <i class="fa fa-envelope"></i>
                                            Import Excel</a>
                                    </li>
                                    <li class="<?php if($this->uri->segment(2)=='all_attendence'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/attendence/all_attendence">
                                            <i class="fa fa-envelope"></i>
                                            All Attendence</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='myattendence'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/myattendence">
                                    <i class="fa fa-calendar"></i>
                                    <span class="title">
                            My Attendence </span>
                                    <span class="selected"></span>
                                </a>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='holidays'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/holidays">
                                    <i class="fa fa-calendar"></i>
                                    <span class="title">
                            Holidays </span>
                                    <span class="selected"></span>
                                </a>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='teachers'){echo 'active';}?>">
                                <a href="javascript:;">
                                    <i class="icon-users"></i>
                                    <span class="title">Staff</span>
                                    <span class="selected"></span>
                                    <span class="arrow <?php if($this->uri->segment(1)=='teachers'){echo 'open';}?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="<?php if($this->uri->segment(2)=='add_teacher'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/teachers/add_teacher">
                                            <i class="icon-plus"></i>
                                            Add Staff</a>
                                    </li>
                                    <li class="<?php if($this->uri->segment(2)=='all_teachers'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/teachers/all_teachers">
                                            <i class="icon-list"></i>
                                            All Staff</a>
                                    </li>
                                    <li class="<?php if($this->uri->segment(2)=='contact_for_fee'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/teachers/contact_for_fee">
                                            <i class="icon-users"></i>
                                            Contact For Fee</a>
                                    </li>
                                </ul>
                            </li>
							
							<li class="<?php if($this->uri->segment(1)=='closings'){echo 'active';}?>">
                                <a href="javascript:;">
                                    <i class="icon-list"></i>
                                    <span class="title">Closings</span>
                                    <span class="selected"></span>
                                    <span class="arrow <?php if($this->uri->segment(1)=='closings'){echo 'open';}?>"></span>
                                </a>
								
                                <ul class="sub-menu">
                                    <li class="<?php if($this->uri->segment(2)=='index'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/closing/closereport">
                                            <i class="icon-plus"></i>
                                            Closings List</a>
                                    </li>
                                    
                                </ul>
                            </li>
							
                            <li class="<?php if($this->uri->segment(1)=='departments'){echo 'active';}?>">
                                <a href="javascript:;">
                                    <i class="icon-list"></i>
                                    <span class="title">Departments</span>
                                    <span class="selected"></span>
                                    <span class="arrow <?php if($this->uri->segment(1)=='departments'){echo 'open';}?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="<?php if($this->uri->segment(2)=='add_department'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/departments/add_department">
                                            <i class="icon-plus"></i>
                                            Add Department</a>
                                    </li>
                                    <li class="<?php if($this->uri->segment(2)=='all_designations'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/departments/all_departments">
                                            <i class="icon-list"></i>
                                            All Departments</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='designations'){echo 'active';}?>">
                                <a href="javascript:;">
                                    <i class="icon-users"></i>
                                    <span class="title">Designations</span>
                                    <span class="selected"></span>
                                    <span class="arrow <?php if($this->uri->segment(1)=='designations'){echo 'open';}?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="<?php if($this->uri->segment(2)=='add_designation'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/designations/add_designation">
                                            <i class="icon-plus"></i>
                                            Add Designation</a>
                                    </li>
                                    <li class="<?php if($this->uri->segment(2)=='all_designations'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/designations/all_designations">
                                            <i class="icon-list"></i>
                                            All Designations</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='loans'){echo 'active';}?>">
                                <a href="javascript:;">
                                    <i class="fa fa-home"></i>
                                    <span class="title">Loans / Advances</span>
                                    <span class="selected"></span>
                                    <span class="arrow <?php if($this->uri->segment(1)=='loans'){echo 'open';}?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="<?php if($this->uri->segment(2)=='apply_loan'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/loans/apply_loan">
                                            <i class="fa fa-dedent"></i>
                                            Loans</a>
                                    </li>


                                    <li class="<?php if($this->uri->segment(2)=='loans_list'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/loans/loans_list">
                                            <i class="fa fa-arrow-down"></i>
                                            Loans Approval</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='staff_type'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="icon-users"></i>
                            <span class="title">Staff Type</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='staff_type'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_staff_type'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/staff_type/add_staff_type">
                                    <i class="icon-plus"></i>
                                    Add Staff Type</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_staff_types'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/staff_type/all_staff_type">
                                    <i class="icon-list"></i>
                                    All Staff Type</a>
                            </li>
                        </ul>
                    </li>
                            <li class="<?php if($this->uri->segment(1)=='allownces'){echo 'active';}?>">
                                <a href="javascript:;">
                                    <i class="fa fa-users"></i>
                                    <span class="title">Allownces</span>
                                    <span class="selected"></span>
                                    <span class="arrow <?php if($this->uri->segment(1)=='allownces'){echo 'open';}?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="<?php if($this->uri->segment(1)=='allownces' && $this->uri->segment(2)==''){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/Allownces/index">
                                            <i class="icon-plus"></i>
                                            Define Allowances</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='salary'){echo 'active';}?>">
                                <a href="javascript:;">
                                    <i class="fa fa-users"></i>
                                    <span class="title">salary Module</span>
                                    <span class="selected"></span>
                                    <span class="arrow <?php if($this->uri->segment(1)=='salary'){echo 'open';}?>"></span>
                                </a>
                                <ul class="sub-menu">
                                    <li class="<?php if($this->uri->segment(1)=='salary' && $this->uri->segment(2)==''){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/salary/salary_list">
                                            <i class="icon-plus"></i>
                                            Salary List</a>
                                    </li>
									<li class="<?php if($this->uri->segment(1)=='salary' && $this->uri->segment(2)==''){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/salary/salary_report">
                                            <i class="icon-plus"></i>
                                            Salary Report</a>
                                    </li>
                                    <li class="<?php if($this->uri->segment(1)=='salary' && $this->uri->segment(2)=='minimum_salary_adjustment_report'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/salary/minimum_salary_adjustment_report">
                                            <i class="icon-plus"></i>
                                            Salary Adjustment Report</a>
                                    </li>
                                </ul>
                            </li>

                        </ul>

                    </li>


                    <li class="<?php if($this->uri->segment(1)=='campuses'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-list"></i>
                            <span class="title">Campuses</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='campuses'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_campus'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/campuses/add_campus">
                                    <i class="icon-plus"></i>
                                    Add Campus</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_campuses'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/campuses/all_campuses">
                                    <i class="icon-list"></i>
                                    All Campuses</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='manage_campus_profit'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/campuses/manage_campus_profit">
                                    <i class="icon-list"></i>
                                    Manage Campus Profit</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='classes'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="icon-map"></i>
                            <span class="title">Classes</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='classes'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_class'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/classes/add_class">
                                    <i class="icon-plus"></i>
                                    Add Class</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_classes'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/classes/all_classes">
                                    <i class="icon-list"></i>
                                    All Classes</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='courses' || $this->uri->segment(1)=='subjects' || $this->uri->segment(1)=='chapters' || $this->uri->segment(1)=='topics'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="icon-map"></i>
                            <span class="title">Courses Management</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='courses' || $this->uri->segment(1)=='subjects' || $this->uri->segment(1)=='chapters' || $this->uri->segment(1)=='topics'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_course'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/courses/add_course">
                                    <i class="icon-plus"></i>
                                    Add Course</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_courses'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/courses/all_courses">
                                    <i class="icon-plus"></i>
                                    All Courses</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_subject'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/subjects/add_subject">
                                    <i class="icon-plus"></i>
                                    Add Subject</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_subjects'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/subjects/all_subjects">
                                    <i class="icon-list"></i>
                                    All Subjects</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_chapter'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/chapters/add_chapter">
                                    <i class="icon-plus"></i>
                                    Add Chapter</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_chapters'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/chapters/all_chapters">
                                    <i class="icon-list"></i>
                                    All Chapters</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_topic'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/topics/add_topic">
                                    <i class="icon-plus"></i>
                                    Add Topic</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_topics'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/topics/all_topics">
                                    <i class="icon-list"></i>
                                    All Topics</a>
                            </li>
                            <!--<li class="<?php if($this->uri->segment(2)=='all_classes'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/courses/add_syllabus">
							<i class="icon-plus"></i>
							Add Syllabus</a>
						</li>-->
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='schedule'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="icon-map"></i>
                            <span class="title">Schedule Management</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='schedule'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_syllabus'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/schedule/add_syllabus">
                                    <i class="icon-plus"></i>
                                    Add Syllabus</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_syllabus'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/schedule/all_syllabus">
                                    <i class="icon-plus"></i>
                                    All Syllabus</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='test_engine'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-cogs"></i>
                            <span class="title">Test Engine</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='test_engine'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='subjects'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/test_engine/subjects">
                                    <i class="icon-plus"></i>
                                    Add Practical &amp; Books</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='topics'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/test_engine/topics">
                                    <i class="icon-plus"></i>
                                    Add Questions</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='upload'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/test_engine/upload">
                                    <i class="fa fa-image"></i>
                                    Upload Image</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='collegepapers'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-list"></i>
                            <span class="title">Papers &amp; Results</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='collegepapers'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_paper'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/collegepapers/add_paper">
                                    <i class="icon-plus"></i>
                                    Add Paper</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_expenses'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/collegepapers/all_paper">
                                    <i class="icon-list"></i>
                                    All Papers</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='student_results'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/collegepapers/student_results">
                                    <i class="icon-list"></i>
                                    Student Results</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='assignments'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-book"></i>
                            <span class="title">Assignments</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='assignments'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_assignment'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/assignments/add_assignment">
                                    <i class="icon-plus"></i>
                                    Add Assignment</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_assignments'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/assignments/all_assignments">
                                    <i class="icon-list"></i>
                                    All Assignments</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='uncheck_assignments'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/assignments/uncheck_assignments">
                                    <i class="fa fa-close"></i>
                                    Uncheck Assignments</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='check_assignments'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/assignments/check_assignments">
                                    <i class="fa fa-check"></i>
                                    Check Assignments</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='reports'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-users"></i>
                            <span class="title">Reports</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='reports'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='students_fee_problem'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/reports/students_fee_problem">
                                    <i class="fa fa-money"></i>
                                    Students Fee Problem</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='discount_report'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/reports/discount_report">
                                    <i class="fa fa-percent"></i>
                                    Discount Report</a>
                            </li>



                            <li class="<?php if($this->uri->segment(2)=='all_struckofstudent_report'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/students/all_struckofstudent_report">
                                    <i class="icon-list"></i>
                                    Struck of Students Report</a>
                            </li>




                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='recovery_management'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-users"></i>
                            <span class="title">Incentive Management</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='recovery_management'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='assign_task'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/recovery_management/assign_task">
                                    <i class="fa fa-plus"></i>
                                    Add User Comission</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_assign_task'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/recovery_management/all_assign_task">
                                    <i class="fa fa-list"></i>
                                    All Users Comission</a>
                            </li>
                            
                            <li class="<?php if($this->uri->segment(2)=='all_assign_task'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/admission_management/all_assign_task">
                                    <i class="fa fa-list"></i>
                                    All Users Admissions Comission</a>
                            </li>

                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='rules'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="icon-lock"></i>
                            <span class="title">Rules</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='rules'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='campus_rules'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/rules/campus_rules">
                                    <i class="fa fa-list"></i>
                                    Campus Rules</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='fee_rules'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/rules/fee_rules">
                                    <i class="fa fa-money"></i>
                                    Add Fee Rules</a>
                            </li>
							<li class="<?php if($this->uri->segment(2)=='all_fee_rules'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/rules/all_fee_rules">
                                    <i class="fa fa-money"></i>
                                    All Fee Rules</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='online_study_rules'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/rules/online_study_rules">
                                    <i class="fa fa-graduation-cap"></i>
                                    Online Study Rules</a>
                            </li>
							<li class="<?php if($this->uri->segment(2)=='closing_rules'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/rules/closing_rules">
                                    <i class="fa fa-graduation-cap"></i>
                                    Daily Closing Rules</a>
                            </li>
							<li class="<?php if($this->uri->segment(2)=='assign_task'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/admission_management/assign_task">
                                    <i class="fa fa-plus"></i>
                                    Admission Incentive Rules</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='students'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-graduation-cap"></i>
                            <span class="title">Students</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='students'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_student'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/students/add_student">
                                    <i class="icon-plus"></i>
                                    Add Student</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_students'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/students/all_students">
                                    <i class="icon-list"></i>
                                    All Students</a>
                            </li>



                        </ul>
                    </li>

					 <li class="<?php if($this->uri->segment(1)=='timetable'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-graduation-cap"></i>
                            <span class="title">Timetable</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='timetable'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='studytype'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/timetable/studytype">
                                    <i class="icon-plus"></i>
                                    Study Type</a>
                            </li>

                            <li class="<?php if($this->uri->segment(2)=='shifts'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/timetable/shifts">
                                    <i class="icon-plus"></i>
                                    Shift</a>
                            </li>
							<li class="<?php if($this->uri->segment(2)=='timetable'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/timetable/timetable">
                                    <i class="icon-list"></i>
                                    Time Table</a>
                            </li>
							<li class="<?php if($this->uri->segment(2)=='all_rooms'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/inventory/all_rooms">
                                    <i class="icon-list"></i>
                                    Add Room</a>
                            </li>

                        </ul>
                    </li>

                    <li class="<?php if($this->uri->segment(1)=='contractors'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-star"></i>
                            <span class="title">Contractors</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='contractors'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_contractor'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/contractors/add_contractor">
                                    <i class="icon-plus"></i>
                                    Add Contractor</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_contractors'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/contractors/all_contractors">
                                    <i class="icon-list"></i>
                                    All Contractors</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='create_contract'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/contractors/create_contract">
                                    <i class="icon-plus"></i>
                                    Create Contract</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_contracts'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/contractors/all_contracts">
                                    <i class="icon-list"></i>
                                    All Contracts</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='visitors'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-users"></i>
                            <span class="title">Visitors</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='visitors'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
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
                    <li class="<?php if($this->uri->segment(1)=='archive'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-archive"></i>
                            <span class="title">Archive</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='visitors'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='teachers'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/archive/teachers">
                                    <i class="icon-users"></i>
                                    Teachers</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='campuses'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/archive/campuses">
                                    <i class="fa fa-list"></i>
                                    Campuses</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='classes'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/archive/classes">
                                    <i class="icon-map"></i>
                                    Classes</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='subjects'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/archive/subjects">
                                    <i class="fa fa-book"></i>
                                    Subjects</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='students'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/archive/students">
                                    <i class="fa fa-graduation-cap"></i>
                                    Students</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='expenses'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-money"></i>
                            <span class="title">Expenses</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='expenses'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_expense'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/expenses/add_expense">
                                    <i class="icon-plus"></i>
                                    Add Expense</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_expenses'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/expenses/all_expenses">
                                    <i class="icon-list"></i>
                                    All Expenses</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='category'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/expenses/category">
                                    <i class="fa fa-sitemap"></i>
                                    Manage Category</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='fees'){echo 'active';}?>">
                        <a href="<?php echo site_url();?>/fees">
                            <i class="fa fa-money"></i>
                            <span class="title">
					Fees Dues </span>
                            <span class="selected"></span>
                        </a>
                    </li>
                    <li class="start <?php if($this->uri->segment(1)=='students_performance'){echo 'active';}?>">
                        <a href="<?php echo site_url();?>/students_performance">
                            <i class="fa fa-pie-chart"></i>
                            <span class="title">
					Students Performance </span>
                            <span class="selected"></span>
                        </a>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='council_list'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-users"></i>
                            <span class="title">Council List</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='council_list'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(1)=='council_list' && $this->uri->segment(2)==''){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/council_list">
                                    <i class="icon-plus"></i>
                                    Create Council List</a>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='council_list' && $this->uri->segment(2)=='fee_detail'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/council_list/fee_detail">
                                    <i class="fa fa-money"></i>
                                    Council List With Fee</a>
                            </li>

                            <li class="<?php if($this->uri->segment(1)=='council_list' && $this->uri->segment(2)=='print_councel'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/council_list/print_councel">
                                    <i class="fa fa-money"></i>
                                    Council List Print</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='sms'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-envelope"></i>
                            <span class="title">Sms</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='sms'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='setup'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/sms/setup">
                                    <i class="fa fa-cog"></i>
                                    SMS Setup</a>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='sms' && $this->uri->segment(2)==''){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/sms">
                                    <i class="icon-plus"></i>
                                    Send SMS</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_sms'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/sms/all_sms">
                                    <i class="icon-list"></i>
                                    All SMS</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='advertisement_sms'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/sms/advertisement_sms">
                                    <i class="fa fa-envelope"></i>
                                    Advertisement SMS</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='punjab_council_roll_number' || $this->uri->segment(1)=='next_council_admissions'){echo 'active';}?>">
                        <!--<a href="<?php echo site_url();?>/punjab_council_roll_number">-->
                        <a href="javascript:;">
                            <i class="fa fa-list"></i>
                            <span class="title">
					Punjab Council </span>
                            <span class="arrow <?php if($this->uri->segment(1)=='punjab_council_roll_number' || $this->uri->segment(1)=='next_council_admissions'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(1)=='punjab_council_roll_number' && $this->uri->segment(2)==''){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/punjab_council_roll_number">
                                    <i class="fa fa-plus"></i>
                                    Enter Punjab Council Roll no</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='result'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/punjab_council_roll_number/result">
                                    <i class="icon-list"></i>
                                    Enter Punjab Council Result</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='final_result'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/punjab_council_roll_number/final_result">
                                    <i class="icon-list"></i>
                                    Final Result Pharmacy Technician</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_council_fee'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/punjab_council_roll_number/add_council_fee">
                                    <i class="fa fa-money"></i>
                                    Add Council Fee Campus Wise</a>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='next_council_admissions'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/next_council_admissions">
                                    <i class="fa fa-money"></i>
                                    Add Council Fee Single Student</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='appear_in_next_exam'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/punjab_council_roll_number/appear_in_next_exam">
                                    <i class="fa fa-list"></i>
                                    Next Exam Status</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='status_report'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/punjab_council_roll_number/status_report">
                                    <i class="fa fa-list"></i>
                                    Status (report)</a>
                            </li>
							 <li class="<?php if($this->uri->segment(2)=='council_result_concile'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/punjab_council_roll_number/council_result_concile">
                                    <i class="fa fa-list"></i>
                                    Council Result Conciliation</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='documents'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-list"></i>
                            <span class="title">
					Documents </span>
                            <span class="arrow <?php if($this->uri->segment(1)=='documents'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(1)=='documents' && $this->uri->segment(2)=='diploma_documents'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/documents/diploma_documents">
                                    <i class="fa fa-list"></i>
                                    Dimploma Documents</a>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='documents' && $this->uri->segment(2)=='students_documents'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/documents/students_documents">
                                    <i class="fa fa-list"></i>
                                    Students Documents</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='documents' && $this->uri->segment(2)=='select_campuse_book'){echo 'active';} ?>">
                                <a href="<?php echo site_url();?>/documents/select_campuse_book">
                                    <i class="fa fa-dedent"></i>
                                    Print View
                                </a>
                            </li>

                            <li class="<?php if($this->uri->segment(2)=='documents' && $this->uri->segment(2)=='recipt_pad_list'){echo 'active';} ?>">
                                <a href="<?php echo site_url();?>/documents/recipt_pad_list">
                                    <i class="fa fa-dedent"></i>
                                    Recipt Pad List
                                </a>
                            </li>

                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='AdvertisementDevices'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-home"></i>
                            <span class="title">Advertisement</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='AdvertisementDevices'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='index'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/AdvertisementDevices/index">
                                    <i class="fa fa-dedent"></i>
                                    Devices List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='accounts' || $this->uri->segment(1)=='pettycash'){echo 'active';}?>">
                            <a href="javascript:;">
                                <i class="fa fa-list"></i>
                                <span class="title">Accounts</span>
                                <span class="selected"></span>
                                <span class="arrow <?php if($this->uri->segment(1)=='accounts'){echo 'open';}?>"></span>
                            </a>
                            <ul class="sub-menu">

                                <li class="<?php if($this->uri->segment(2)=='account_details'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/accounts/account_details">
                                        <i class="icon-list"></i>
                                        Account Details</a>
                                </li>

                                <li class="<?php if($this->uri->segment(2)==''){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/accounts">
                                        <i class="fa fa-money"></i>
                                        Manage Accounts</a>
                                </li>
                                <li class="<?php if($this->uri->segment(1)=='pettycash'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/pettycash/index">
                                        <i class="icon-list"></i>
                                        Campus PettyCash</a>
                                </li>
                                <li class="<?php if($this->uri->segment(2)=='advance'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/accounts/advance">
                                        <i class="icon-list"></i>
                                        Advance System</a>
                                </li>
                                <li class="<?php if($this->uri->segment(2)=='accounts_loans_list'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/loans/accounts_loans_list">
                                        <i class="fa fa-arrow-down"></i>
                                        Loans Approval Accounts</a>
                                </li>
								
								<li class="<?php if($this->uri->segment(1)=='closing'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/closing/index">
                                        <i class="fa fa-arrow-down"></i>
                                        Daily Closings</a>
                                </li>
								
                                    <li class="<?php if($this->uri->segment(2)=='index'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/closing/accountsclosing">
                                            <i class="icon-plus"></i>
                                            Closings conciliation</a>
                                    </li>
                                    
                                
								<li class="<?php if($this->uri->segment(1)=='uploadstatement'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/accounts/uploadstatement">
                                        <i class="fa fa-arrow-down"></i>
                                        Statement Reconciliation</a>
                                </li>
                            </ul>
                        </li>


					



					 <li class="<?php if($this->uri->segment(1)=='pettycash'){echo 'active';}?>">
                            <a href="javascript:;">
                                <i class="fa fa-list"></i>
                                <span class="title">Petty CAsh</span>
                                <span class="selected"></span>
                                <span class="arrow <?php if($this->uri->segment(1)=='pettycash'){echo 'open';}?>"></span>
                            </a>
                            <ul class="sub-menu">

                               
                                <li class="<?php if($this->uri->segment(1)=='pettycash'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/pettycash/index">
                                        <i class="icon-list"></i>
                                        Campus PettyCash</a>
                                </li>
								
								 <li class="<?php if($this->uri->segment(2)=='closing_person'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/closing/closing_person">
                                        <i class="icon-list"></i>
                                        Campus Closing Persons</a>
                                </li>
                               
                            </ul>
                        </li>

                    <li class="<?php if($this->uri->segment(1)=='reminders'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-users"></i>
                            <span class="title">Reminders</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='reminders'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_reminder'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/reminders/add_reminder">
                                    <i class="icon-plus"></i>
                                    Add Reminder Rules</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_reminders'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/reminders/all_reminders">
                                    <i class="icon-list"></i>
                                    All Reminders Rules</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_pending_reminder'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/reminders/all_pending_reminder">
                                    <i class="icon-list"></i>
                                    All Pending Reminders</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_completed_reminder'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/reminders/all_completed_reminder">
                                    <i class="icon-list"></i>
                                    All Completed Reminders</a>
                            </li>
                        </ul>
                    </li>
                    <?php if(@$myAccess[0]['construction_sidebar']==1 || $this->session->userdata('role')=='Admin'): ?>
                    <li class="<?php if($this->uri->segment(1)=='construction'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-building"></i>
                            <span class="title">Construction</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='construction'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <?php if(@$myAccess[0]['construction_dashboard']==1 || $this->session->userdata('role')=='Admin'): ?>
                            <li><a href="<?php echo site_url();?>/construction"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                            <?php endif; ?>
                            <?php if(@$myAccess[0]['construction_projects']==1 || $this->session->userdata('role')=='Admin'): ?>
                            <li><a href="<?php echo site_url();?>/construction/projects"><i class="icon-plus"></i> Projects</a></li>
                            <?php endif; ?>
                            <?php if(@$myAccess[0]['construction_boq']==1 || $this->session->userdata('role')=='Admin'): ?>
                            <li><a href="<?php echo site_url();?>/construction/boq"><i class="fa fa-list"></i> BOQ / Estimate</a></li>
                            <?php endif; ?>
                            <?php if(@$myAccess[0]['construction_work']==1 || $this->session->userdata('role')=='Admin'): ?>
                            <li><a href="<?php echo site_url();?>/construction/work"><i class="fa fa-plus"></i> Site Work</a></li>
                            <?php endif; ?>
                            <?php if(@$myAccess[0]['construction_contractors']==1 || $this->session->userdata('role')=='Admin'): ?>
                            <li><a href="<?php echo site_url();?>/construction/contractors"><i class="fa fa-briefcase"></i> Contractors</a></li>
                            <?php endif; ?>
                            <?php if(@$myAccess[0]['construction_reports']==1 || $this->session->userdata('role')=='Admin'): ?>
                            <li><a href="<?php echo site_url();?>/construction/reports"><i class="fa fa-file"></i> Reports</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li class="<?php if($this->uri->segment(1)=='inventory'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-barcode"></i>
                            <span class="title">Inventory</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='inventory'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_room'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/inventory/add_room">
                                    <i class="icon-plus"></i>
                                    Add Room</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_rooms'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/inventory/all_rooms">
                                    <i class="icon-list"></i>
                                    All Rooms</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_subroom'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/inventory/add_subroom">
                                    <i class="icon-plus"></i>
                                    Add Sub-Room</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_subrooms'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/inventory/all_subrooms">
                                    <i class="icon-list"></i>
                                    All Sub-Rooms</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_product_name'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/inventory/add_product_name">
                                    <i class="icon-plus"></i>
                                    Manage Product Name</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_document_name'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/inventory/add_document_name">
                                    <i class="icon-plus"></i>
                                    Manage Document Name</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_product'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/inventory/add_product">
                                    <i class="icon-plus"></i>
                                    Add Product</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_products'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/inventory/all_products">
                                    <i class="icon-list"></i>
                                    All Products</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_document'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/inventory/add_document">
                                    <i class="icon-plus"></i>
                                    Add Document</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_documents'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/inventory/all_documents">
                                    <i class="icon-list"></i>
                                    All Documents</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?php if($this->uri->segment(1)=='downloads' || $this->uri->segment(1)=='events' || $this->uri->segment(1)=='event_images' || $this->uri->segment(1)=='slider_images' || $this->uri->segment(1)=='news_updates' || $this->uri->segment(1)=='faqs' || $this->uri->segment(1)=='videos' || $this->uri->segment(1)=='zoom'){echo 'active';}?>">
                        <a href="javascript:;">
                            <i class="fa fa-globe"></i>
                            <span class="title">Website</span>
                            <span class="selected"></span>
                            <span class="arrow <?php if($this->uri->segment(1)=='downloads' || $this->uri->segment(1)=='events' || $this->uri->segment(1)=='event_images' || $this->uri->segment(1)=='slider_images' || $this->uri->segment(1)=='news_updates' || $this->uri->segment(1)=='faqs' || $this->uri->segment(1)=='videos'){echo 'open';}?>"></span>
                        </a>
                        <ul class="sub-menu">
                            <li class="<?php if($this->uri->segment(2)=='add_download'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/downloads/add_download">
                                    <i class="icon-plus"></i>
                                    Add Download</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_downloads'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/downloads/all_downloads">
                                    <i class="icon-list"></i>
                                    All Downloads</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_event'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/events/add_event">
                                    <i class="icon-plus"></i>
                                    Add Event</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_events'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/events/all_events">
                                    <i class="icon-list"></i>
                                    All Event</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_event_image'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/event_images/add_event_image">
                                    <i class="icon-plus"></i>
                                    Add Event Image</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_images'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/event_images/all_images">
                                    <i class="icon-list"></i>
                                    All Event Images</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_slider_image'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/slider_images/add_slider_image">
                                    <i class="icon-plus"></i>
                                    Add Slider Image</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_images'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/slider_images/all_images">
                                    <i class="icon-list"></i>
                                    All Slider Images</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='add_news'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/news_updates/add_news">
                                    <i class="icon-plus"></i>
                                    Add News</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='all_news'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/news_updates/all_news">
                                    <i class="icon-list"></i>
                                    All News</a>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='faqs'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/faqs">
                                    <i class="fa fa-question"></i>
                                    Faqs</a>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='videos'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/videos">
                                    <i class="fa fa-film"></i>
                                    Videos</a>
                            </li>
                            <li class="<?php if($this->uri->segment(2)=='home_page'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/pages/home_page">
                                    <i class="fa fa-home"></i>
                                    Home Page</a>
                            </li>
                            <li class="<?php if($this->uri->segment(1)=='zoom'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/zoom">
                                    <i class="fa fa-desktop"></i>
                                    Zoom Setup</a>
                            </li>
                        </ul>
                    </li>

                </ul>
			
			<?php
            	else:
			?>
            <ul class="page-sidebar-menu " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
				<!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
				<li class="sidebar-toggler-wrapper">
					<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
					<div class="sidebar-toggler">
					</div>
					<!-- END SIDEBAR TOGGLER BUTTON -->
				</li>
				<!-- DOC: To remove the search box from the sidebar you just need to completely remove the below "sidebar-search-wrapper" LI element -->
				<li class="sidebar-search-wrapper">
					<!-- BEGIN RESPONSIVE QUICK SEARCH FORM -->
					<!-- DOC: Apply "sidebar-search-bordered" class the below search form to have bordered search box -->
					<!-- DOC: Apply "sidebar-search-bordered sidebar-search-solid" class the below search form to have bordered & solid search box -->
					<!--<form class="sidebar-search " action="extra_search.html" method="POST">
						<a href="javascript:;" class="remove">
						<i class="icon-close"></i>
						</a>
						<div class="input-group">
							<input type="text" class="form-control" placeholder="Search...">
							<span class="input-group-btn">
							<a href="javascript:;" class="btn submit"><i class="icon-magnifier"></i></a>
							</span>
						</div>
					</form>-->
					<!-- END RESPONSIVE QUICK SEARCH FORM -->
                    <br />
				</li>
                <li class="start <?php if($this->uri->segment(1)=='dashboard'){echo 'active';}?>">
					<a href="<?php echo base_url()?>">
					<i class="icon-home"></i>
					<span class="title">
					<?php $reminders_count=$this->db->get_where('reminder',array('user_id'=>$this->session->userdata('user_id'),'check_by_admin'=>0))->result_array();?>
					Dashboard <?php if(count($reminders_count)>0):?><span class="badge badge-warning"><?php echo count($reminders_count);?></span><?php endif;?></span>
                    <span class="selected"></span>
					</a>
				</li>
                <?php
                	if(@$myAccess[0]['online_application_access']==1):
				?>
                <?php
                	//$total_new_online_applications = $this->db->get_where('apply_now', array('status'=>0))->result_array();
					//$total_clear_online_applications = $this->db->get_where('apply_now', array('status'=>1))->result_array();
				?>
                <li class="<?php if($this->uri->segment(1)=='online_application'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-comment"></i>
					<span class="title">Online Application</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='online_application'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
                        	if(@$myAccess[0]['online_application_new_admissions']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='new_applications'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/online_application/new_applications">
							<i class="fa fa-envelope"></i>
							<span class="badge badge-danger"><?php echo newApplicationsCount();?></span>New Applications</a>
						</li>
						<li class="<?php if($this->uri->segment(2)=='pending_applications'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/online_application/pending_applications">
							<i class="fa fa-envelope"></i>
							<span class="badge badge-danger"><?php echo pendingApplicationsCount();?></span>Pending Applications</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
                        	if(@$myAccess[0]['online_application_checked_admissions']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='checked_applications'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/online_application/checked_applications">
							<i class="fa fa-check"></i>
                            Checked Applications</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
                        	if(@$myAccess[0]['online_application_all']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='all_applications'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/online_application/all_applications">
							<i class="fa fa-list"></i>
							All Applications</a>
						</li>
						
						<li class="<?php if($this->uri->segment(2)=='confirmed_admissions'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/online_application/confirmed_admissions">
							<i class="fa fa-list"></i>
							Confirmed Admissions</a>
						</li>
                        <li class="<?php if($this->uri->segment(2)=='dynamic_forms'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/online_application/dynamic_forms">
							<i class="fa fa-wpforms"></i>
							Dynamic Forms</a>
						</li>
                        <?php
                        	endif;
						?>
						 <?php
                        	if(@$myAccess[0]['facebook_leads']==1):
						?>
						
						 <li class="<?php if($this->uri->segment(2)=='upload_fb_leads'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/online_application/upload_fb_leads">
                                    <i class="fa fa-upload"></i>
                                    Upload Facebook Leads</a>
                            </li>
						<?php
                        	endif;
						?>
						
					</ul>
				</li>
                <?php
                	endif;
				?>
				
				
				
				
				<li class="<?php if($this->uri->segment(1)=='loans'){echo 'active';}?>">
                    <a href="javascript:;">
                        <i class="fa fa-home"></i>
                        <span class="title">Loans / Advances</span>
                        <span class="selected"></span>
                        <span class="arrow <?php if($this->uri->segment(1)=='loans'){echo 'open';}?>"></span>
                    </a>
                    <ul class="sub-menu">
                        <li class="<?php if($this->uri->segment(2)=='apply_loan'){echo 'active';}?>">
                            <a href="<?php echo site_url();?>/loans/apply_loan">
                                <i class="fa fa-dedent"></i>
                                Loans</a>
                        </li>
						<?php if(@$myAccess[0]['loan_approval']==1): ?>

                        <li class="<?php if($this->uri->segment(2)=='loans_list'){echo 'active';}?>">
                            <a href="<?php echo site_url();?>/loans/loans_list">
                                <i class="fa fa-arrow-down"></i>
                                Loans Approval</a>
                        </li>
						
						<li class="<?php if($this->uri->segment(2)=='accounts_loans_list'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/loans/accounts_loans_list">
                                    <i class="fa fa-arrow-down"></i>
                                    Loans Approval Accounts</a>
                            </li>
						<?php endif ?>
                    </ul>
                </li>
				
				<?php if (@$myAccess[0]['accounts_sidebar']==1): ?>
                        <li class="<?php if($this->uri->segment(1)=='accounts' || $this->uri->segment(1)=='pettycash'){echo 'active';}?>">
                            <a href="javascript:;">
                                <i class="fa fa-list"></i>
                                <span class="title">Accounts</span>
                                <span class="selected"></span>
                                <span class="arrow <?php if($this->uri->segment(1)=='accounts'){echo 'open';}?>"></span>
                            </a>
                            <ul class="sub-menu">

                                <li class="<?php if($this->uri->segment(2)=='account_details'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/accounts/account_details">
                                        <i class="icon-list"></i>
                                        Account Details</a>
                                </li>

                                <li class="<?php if($this->uri->segment(2)==''){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/accounts">
                                        <i class="fa fa-money"></i>
                                        Manage Accounts</a>
                                </li>
                                <li class="<?php if($this->uri->segment(1)=='pettycash'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/pettycash/index">
                                        <i class="icon-list"></i>
                                        Campus PettyCash</a>
                                </li>
                                <li class="<?php if($this->uri->segment(2)=='advance'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/accounts/advance">
                                        <i class="icon-list"></i>
                                        Advance System</a>
                                </li>
                                <li class="<?php if($this->uri->segment(2)=='accounts_loans_list'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/loans/accounts_loans_list">
                                        <i class="fa fa-arrow-down"></i>
                                        Loans Approval Accounts</a>
                                </li>
								<?php
								if(@$myAccess[0]['dailyclosing']==1):	?>
								<li class="<?php if($this->uri->segment(1)=='closing'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/closing/index">
                                        <i class="fa fa-arrow-down"></i>
                                        Daily Closings</a>
                                </li>
								<?php endif; ?>
								<?php
								if(@$myAccess[0]['closing_reconcile']==1):	?>
								 <li class="<?php if($this->uri->segment(2)=='index'){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/closing/accountsclosing">
                                            <i class="icon-plus"></i>
                                            Closings conciliation</a>
                                    </li>
								<?php endif; ?>
                            </ul>
                        </li>


                    <?php endif; ?>
					
					
					
					
					<?php if (@$myAccess[0]['pettycash_sidebar']==1): ?>
					
					
					<li class="<?php if($this->uri->segment(1)=='pettycash'){echo 'active';}?>">
                            <a href="javascript:;">
                                <i class="fa fa-list"></i>
                                <span class="title">Petty Cash</span>
                                <span class="selected"></span>
                                <span class="arrow <?php if($this->uri->segment(1)=='pettycash'){echo 'open';}?>"></span>
                            </a>
                            <ul class="sub-menu">

                               
                                <li class="<?php if($this->uri->segment(1)=='pettycash'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/pettycash/index">
                                        <i class="icon-list"></i>
                                        Campus PettyCash</a>
                                </li>
								<?php if (@$myAccess[0]['add_closing_person']==1): ?>
								 <li class="<?php if($this->uri->segment(2)=='closing_person'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/closing/closing_person">
                                        <i class="icon-list"></i>
                                        Campus Closing Persons</a>
                                </li>
                               
							   <?php endif; ?>
							   
                            </ul>
                        </li>
					
					
					
					<?php endif; ?>
					
				
				<?php
                	if(@$myAccess[0]['attendence_sidebar']==1):
				?>
				<li class="<?php if($this->uri->segment(1)=='attendence'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-users"></i>
					<span class="title">Attendence</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='attendence'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['attendence_add_attendence']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='add_attendence'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/attendence/add_attendence">
							<i class="fa fa-envelope"></i>
							Add Attendence</a>
						</li>
						
						<li class="<?php if($this->uri->segment(2)=='add_attendence'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/excel_import/index">
							<i class="fa fa-envelope"></i>
							Import Excel</a>
						</li>
						
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['attendence_all_attendence']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_attendence'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/attendence/all_attendence">
							<i class="fa fa-envelope"></i>
							All Attendence</a>
						</li>
						<?php
							endif;
						?>
					</ul>
				</li>
				<?php
                	endif;
				?>
				
				<li class="<?php if($this->uri->segment(1)=='myattendence'){echo 'active';}?>">
					<a href="<?php echo site_url();?>/myattendence">
					<i class="fa fa-calendar"></i>
					<span class="title">
					My Attendence </span>
                    <span class="selected"></span>
					</a>
				</li>
				
				<?php
					if(@$myAccess[0]['holidays_sidebar']==1):
				?>
                <li class="start <?php if($this->uri->segment(1)=='holidays'){echo 'active';}?>">
					<a href="<?php echo site_url();?>/holidays">
					<i class="fa fa-calendar"></i>
					<span class="title">
					Holidays </span>
                    <span class="selected"></span>
					</a>
				</li>
                <?php
                	endif;
				?>
				
				<?php
					if(@$myAccess[0]['department_sidebar']==1):
				?>
				<li class="<?php if($this->uri->segment(1)=='departments'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="icon-list"></i>
					<span class="title">Departments</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='departments'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['department_add_department']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='add_department'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/departments/add_department">
							<i class="icon-plus"></i>
							Add Department</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['department_all_department']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_designations'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/departments/all_departments">
							<i class="icon-list"></i>
							All Departments</a>
						</li>
						<?php
							endif;
						?>
					</ul>
				</li>
				<?php
                	endif;
				?>
				
				<?php
					if(@$myAccess[0]['designation_sidebar']==1):
				?>
				<li class="<?php if($this->uri->segment(1)=='designations'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="icon-users"></i>
					<span class="title">Designations</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='designations'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['designation_add_designation']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='add_designation'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/designations/add_designation">
							<i class="icon-plus"></i>
							Add Designation</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['designation_all_designation']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_designations'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/designations/all_designations">
							<i class="icon-list"></i>
							All Designations</a>
						</li>
						<?php
							endif;
						?>
					</ul>
				</li>
				<?php
                	endif;
				?>
				
				<?php
					if(@$myAccess[0]['staff_type_sidebar']==1):
				?>
				<li class="<?php if($this->uri->segment(1)=='staff_type'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="icon-users"></i>
					<span class="title">Staff Type</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='staff_type'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['staff_type_add_staff_type']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='add_staff_type'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/staff_type/add_staff_type">
							<i class="icon-plus"></i>
							Add Staff Type</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['staff_type_all_staff_type']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_staff_types'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/staff_type/all_staff_type">
							<i class="icon-list"></i>
							All Staff Type</a>
						</li>
						<?php
							endif;
						?>
					</ul>
				</li>
				<?php
					endif;
				?>
                
                <?php
                	if(@$myAccess[0]['staff_sidebar']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='teachers'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="icon-users"></i>
					<span class="title">Staff</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='teachers'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['staff_add']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='add_teacher'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/teachers/add_teacher">
							<i class="icon-plus"></i>
							Add Staff</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
							if(@$myAccess[0]['staff_all']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_teachers'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/teachers/all_teachers">
							<i class="icon-list"></i>
							All Staff</a>
						</li>
                        <?php
                        	endif;
						?>
					</ul>
				</li>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['class_sidebar']==1):
				?>
				<li class="<?php if($this->uri->segment(1)=='classes'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="icon-map"></i>
					<span class="title">Classes</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='classes'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['class_add']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='add_class'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/classes/add_class">
							<i class="icon-plus"></i>
							Add Class</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
							if(@$myAccess[0]['class_all']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_classes'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/classes/all_classes">
							<i class="icon-list"></i>
							All Classes</a>
						</li>
                        <?php
                        	endif;
						?>
					</ul>
				</li>
                <?php
                	endif;
				?>

                <?php
                if(@$myAccess[0]['assignments_sidebar']==1):
                ?>
				<li class="<?php if($this->uri->segment(1)=='assignments'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-book"></i>
					<span class="title">Assignments</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='assignments'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
                        <?php
                        if(@$myAccess[0]['assignments_add_assignment']==1):
                        ?>
                        <li class="<?php if($this->uri->segment(2)=='add_assignment'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/assignments/add_assignment">
							<i class="icon-plus"></i>
							Add Assignment</a>
						</li>
                        <?php
                        endif;
                        ?>
                        <?php
                        if(@$myAccess[0]['assignments_all_assignments']==1):
                        ?>
						<li class="<?php if($this->uri->segment(2)=='all_assignments'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/assignments/all_assignments">
							<i class="icon-list"></i>
							All Assignments</a>
						</li>
                        <?php
                        endif;
                        ?>
                        <?php
                        if(@$myAccess[0]['assignments_uncheck_assignments']==1):
                        ?>
						<li class="<?php if($this->uri->segment(2)=='uncheck_assignments'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/assignments/uncheck_assignments">
							<i class="fa fa-close"></i>
							Uncheck Assignments</a>
						</li>
                        <?php
                        endif;
                        ?>
                        <?php
                        if(@$myAccess[0]['assignments_check_assignments']==1):
                        ?>
						<li class="<?php if($this->uri->segment(2)=='check_assignments'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/assignments/check_assignments">
							<i class="fa fa-check"></i>
							Check Assignments</a>
						</li>
                        <?php
                        endif;
                        ?>
					</ul>
				</li>
                <?php
                endif;
                ?>

                <?php
					if(@$myAccess[0]['reports_sidebar']==1):
				?>
				<li class="<?php if($this->uri->segment(1)=='reports'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-users"></i>
					<span class="title">Reports</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='reports'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['reports_student_fee_problem']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='students_fee_problem'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/reports/students_fee_problem">
								<i class="fa fa-money"></i>
								Students Fee Problem</a>
						</li>
					<?php
					endif;
					?>
					<?php
					if(@$myAccess[0]['reports_discount_report']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='discount_report'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/reports/discount_report">
								<i class="fa fa-percent"></i>
								Discount Report</a>
						</li>
					<?php
					endif;
					?>
						
						<?php
                        if(@$myAccess[0]['all_struckofstudent_report']==1):
                            ?>
						
				
						
						<li class="<?php if($this->uri->segment(2)=='all_struckofstudent_report'){echo 'active';}?>">
                            <a href="<?php echo site_url();?>/students/all_struckofstudent_report">
                                <i class="icon-list"></i>
                                Struck of Students Report</a>
                        </li>
						
						 <?php
                        	endif;
						?>
						
						
					</ul>
				</li>
				<?php
					endif;
				?>
				<?php
					if(@$myAccess[0]['student_sidebar']==1):
				?>
				<li class="<?php if($this->uri->segment(1)=='students'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-graduation-cap"></i>
					<span class="title">Students</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='students'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['student_add']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='add_student'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/students/add_student">
							<i class="icon-plus"></i>
							Add Student</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
							if(@$myAccess[0]['student_all']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_students'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/students/all_students">
							<i class="icon-list"></i>
							All Students</a>
						</li>

                            <?php
                            endif;
                        ?>

                      
						
						
						
					</ul>
				</li>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['contractor_sidebar']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='contractors'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-star"></i>
					<span class="title">Contractors</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='contractors'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['contractor_add']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='add_contractor'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/contractors/add_contractor">
							<i class="icon-plus"></i>
							Add Contractor</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
							if(@$myAccess[0]['contractor_all']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_contractors'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/contractors/all_contractors">
							<i class="icon-list"></i>
							All Contractors</a>
						</li>
                        <?php
                        	endif;
						?>
					</ul>
				</li>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['visitor_sidebar']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='visitors'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-users"></i>
					<span class="title">Visitors</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='visitors'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['visitor_add']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='add_visitor'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/visitors/add_visitor">
							<i class="icon-plus"></i>
							Add Visitor</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
							if(@$myAccess[0]['visitor_all']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_visitors'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/visitors/all_visitors">
							<i class="icon-list"></i>
							All Visitors</a>
						</li>
                        <?php
                        	endif;
						?>
					</ul>
				</li>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['archive_sidebar']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='archive'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-archive"></i>
					<span class="title">Archive</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='visitors'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<li class="<?php if($this->uri->segment(2)=='teachers'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/archive/teachers">
							<i class="icon-users"></i>
							Teachers</a>
						</li>
                        <li class="<?php if($this->uri->segment(2)=='classes'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/archive/classes">
							<i class="icon-map"></i>
							Classes</a>
						</li>
                        <li class="<?php if($this->uri->segment(2)=='subjects'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/archive/subjects">
							<i class="fa fa-book"></i>
							Subjects</a>
						</li>
						<li class="<?php if($this->uri->segment(2)=='students'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/archive/students">
							<i class="fa fa-graduation-cap"></i> 
							Students</a>
						</li>
					</ul>
				</li>
                <?php
                	endif;
				?>
                <?php
					/*
					if(@$myAccess[0]['attendence_sidebar']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='attendence_data'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-calendar"></i>
					<span class="title">Attendence</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='attendence_data'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<li class="<?php if($this->uri->segment(2)==''){echo 'active';}?>">
							<a href="<?php echo site_url();?>/attendence_data">
							<i class="icon-users"></i>
							Add User</a>
						</li>
						<li class="<?php if($this->uri->segment(2)=='users'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/attendence_data/users">
							<i class="fa fa-users"></i> 
							Check Users</a>
						</li>
					</ul>
				</li>
                <?php
                	endif;
					*/
				?>
                <?php
					if(@$myAccess[0]['expense_sidebar']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='expenses'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-money"></i>
					<span class="title">Expenses</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='expenses'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['expense_add']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='add_expense'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/expenses/add_expense">
							<i class="icon-plus"></i>
							Add Expense</a>
						</li>
                        <?php
							endif;
						?>
                        <?php
							if(@$myAccess[0]['expense_all']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_expenses'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/expenses/all_expenses">
							<i class="icon-list"></i>
							All Expenses</a>
						</li>
                        <?php
							endif;
						?>
						 <?php
							if(@$myAccess[0]['expense_category']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='category'){echo 'active';}?>">
                                <a href="<?php echo site_url();?>/expenses/category">
                                    <i class="fa fa-sitemap"></i>
                                    Manage Category</a>
                        </li>
						<?php
							endif;
						?>
						
					</ul>
				</li>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['fee_due_sidebar']==1):
				?>
                <li class="start <?php if($this->uri->segment(1)=='fees'){echo 'active';}?>">
					<a href="<?php echo site_url();?>/fees">
					<i class="fa fa-money"></i>
					<span class="title">
					Fees Dues </span>
                    <span class="selected"></span>
					</a>
				</li>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['student_performance_sidebar']==1):
				?>
                <li class="start <?php if($this->uri->segment(1)=='students_performance'){echo 'active';}?>">
					<a href="<?php echo site_url();?>/students_performance">
					<i class="fa fa-pie-chart"></i>
					<span class="title">
					Students Performance </span>
                    <span class="selected"></span>
					</a>
				</li>
                <?php
                	endif;
				?>
                
				<?php
					if(@$myAccess[0]['salary']==1):
				?>
				
				<li class="<?php if($this->uri->segment(1)=='salary'){echo 'active';}?>">
                    <a href="javascript:;">
                        <i class="fa fa-users"></i>
                        <span class="title">salaries</span>
                        <span class="selected"></span>
                        <span class="arrow <?php if($this->uri->segment(1)=='salary'){echo 'open';}?>"></span>
                    </a>
                    <ul class="sub-menu">
                        <li class="<?php if($this->uri->segment(1)=='salary' && $this->uri->segment(2)==''){echo 'active';}?>">
                            <a href="<?php echo site_url();?>/salary/salary_list">
                                <i class="icon-plus"></i>
                                Salary List</a>
                        </li>
						<li class="<?php if($this->uri->segment(1)=='salary' && $this->uri->segment(2)==''){echo 'active';}?>">
                                        <a href="<?php echo site_url();?>/salary/salary_report">
                                            <i class="icon-plus"></i>
                                            Salary Report</a>
                                    </li>
                        <li class="<?php if($this->uri->segment(1)=='salary' && $this->uri->segment(2)=='minimum_salary_adjustment_report'){echo 'active';}?>">
                            <a href="<?php echo site_url();?>/salary/minimum_salary_adjustment_report">
                                <i class="icon-plus"></i>
                                Salary Adjustment Report</a>
                        </li>
                    </ul>
                </li>
				
				<?php endif; ?>
				
                <?php
					if(@$myAccess[0]['supply_students_sidebar']==1):
				?>
                <li class="start <?php if($this->uri->segment(1)=='supply'){echo 'active';}?>">
					<a href="<?php echo site_url();?>/supply">
					<i class="fa fa-users"></i>
					<span class="title">
					Add Supply Students </span>
                    <span class="selected"></span>
					</a>
				</li>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['council_list_sidebar']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='council_list'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-users"></i>
					<span class="title">Council List</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='council_list'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['create_council_list']==1):
						?>
                        <li class="<?php if($this->uri->segment(1)=='council_list' && $this->uri->segment(2)==''){echo 'active';}?>">
							<a href="<?php echo site_url();?>/council_list">
							<i class="icon-plus"></i>
							Create Council List</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
							if(@$myAccess[0]['create_council_list_with_fee']==1):
						?>
						<li class="<?php if($this->uri->segment(1)=='council_list' && $this->uri->segment(2)=='fee_detail'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/council_list/fee_detail">
							<i class="fa fa-money"></i>
							Council List With Fee</a>
						</li>
                        <?php
                        	endif;
						?>

					</ul>
				</li>
                <?php
                	endif;
				?>
				
				<?php
					if(@$myAccess[0]['recovery_portal']==1):
					
					
					$this->db->select('*');
					$this->db->from('recovery_management');
					$this->db->where('recovery_management.user_id',$this->session->userdata('user_id'));
					$usb = $this->db->get()->result_array();
					
				?>
					<li class="<?php if($this->uri->segment(1)=='recovery_management'){echo 'active';}?>">
						<a href="javascript:;">
						<i class="fa fa-users"></i>
						<span class="title">Incentive Management</span>
						<span class="selected"></span>
						<span class="arrow <?php if($this->uri->segment(1)=='recovery_management'){echo 'open';}?>"></span>
						</a>
						<ul class="sub-menu">
						
						<?php if(count($usb) > 0){ ?>
							<li class="<?php if($this->uri->segment(2)=='check_recovery'){echo 'active';}?>">
								<a href="<?php 
								echo site_url();?>/recovery_management/check_recovery/<?php echo $usb[0]['recovery_management_id'] ?>">
								<i class="fa fa-plus"></i>
								Recovery Portal</a>
							</li>
						<?php } else{ ?>
							<li class="<?php if($this->uri->segment(2)=='all_assign_task'){echo 'active';}?>">
								<a href="<?php echo site_url();?>/recovery_management/all_assign_task">
								<i class="fa fa-list"></i>
								All Users Comission</a>
							</li>
						<?php } ?>
						<li class="<?php if($this->uri->segment(1)=='closing'){echo 'active';}?>">
                                    <a href="<?php echo site_url();?>/closing/index">
                                        <i class="fa fa-arrow-down"></i>
                                        Daily Closings</a>
                                </li>
							
						</ul>
					</li>
				<?php
					
						endif;
				
				?>
				
                <?php
					if(@$myAccess[0]['event_images']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='event_images'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-image"></i>
					<span class="title">Event Images</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='event_images'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<li class="<?php if($this->uri->segment(2)=='add_event_image'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/event_images/add_event_image">
							<i class="icon-plus"></i>
							Add Event Image</a>
						</li>
						<li class="<?php if($this->uri->segment(2)=='all_images'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/event_images/all_images">
							<i class="icon-list"></i>
							All Event Images</a>
						</li>
					</ul>
				</li>
                <?php
                	endif;
				?>
				
				
				 <?php
					if(@$myAccess[0]['define_allownces']==1):
				?>
                
						 <li class="<?php if($this->uri->segment(1)=='allownces'){echo 'active';}?>">
                    <a href="javascript:;">
                        <i class="fa fa-users"></i>
                        <span class="title">Allownces</span>
                        <span class="selected"></span>
                        <span class="arrow <?php if($this->uri->segment(1)=='allownces'){echo 'open';}?>"></span>
                    </a>
                    <ul class="sub-menu">
                        <li class="<?php if($this->uri->segment(1)=='allownces' && $this->uri->segment(2)==''){echo 'active';}?>">
                            <a href="<?php echo site_url();?>/Allownces/index">
                                <i class="icon-plus"></i>
                                Define Allowances</a>
                        </li>
                    </ul>
                </li>


                
				
				
				
                <?php
                	endif;
				?>
				
				
				
				
                <?php
					if(@$myAccess[0]['slider_images']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='slider_images'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-image"></i>
					<span class="title">Slider Images</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='slider_images'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<li class="<?php if($this->uri->segment(2)=='add_slider_image'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/slider_images/add_slider_image">
							<i class="icon-plus"></i>
							Add Slider Image</a>
						</li>
						<li class="<?php if($this->uri->segment(2)=='all_images'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/slider_images/all_images">
							<i class="icon-list"></i>
							All Slider Images</a>
						</li>
					</ul>
				</li>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['news_updates']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='news_updates'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-image"></i>
					<span class="title">News Updates</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='news_updates'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<li class="<?php if($this->uri->segment(2)=='add_news'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/news_updates/add_news">
							<i class="icon-plus"></i>
							Add News</a>
						</li>
						<li class="<?php if($this->uri->segment(2)=='all_images'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/news_updates/all_news">
							<i class="icon-list"></i>
							All News</a>
						</li>
					</ul>
				</li>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['campuses']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='campuses'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-list"></i>
					<span class="title">Campuses</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='campuses'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<li class="<?php if($this->uri->segment(2)=='add_campus'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/campuses/add_campus">
							<i class="icon-plus"></i>
							Add Campus</a>
						</li>
						<li class="<?php if($this->uri->segment(2)=='all_campuses'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/campuses/all_campuses">
							<i class="icon-list"></i>
							All Campuses</a>
						</li>
					</ul>
				</li>
                <?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['sms']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='sms'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-envelope"></i>
					<span class="title">Sms</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='sms'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<li class="<?php if($this->uri->segment(2)=='setup'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/sms/setup">
							<i class="fa fa-cog"></i>
							SMS Setup</a>
						</li>
                        <li class="<?php if($this->uri->segment(1)=='sms' && $this->uri->segment(2)==''){echo 'active';}?>">
							<a href="<?php echo site_url();?>/sms">
							<i class="icon-plus"></i>
							Send SMS</a>
						</li>
						<li class="<?php if($this->uri->segment(2)=='all_sms'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/sms/all_sms">
							<i class="icon-list"></i>
							All SMS</a>
						</li>
					</ul>
				</li>
                <?php
                	endif;
				?>
                
                <?php
					if(@$myAccess[0]['punjab_pharmacy_council_access']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='punjab_council_roll_number'){echo 'active';}?>">
					<!--<a href="<?php echo site_url();?>/punjab_council_roll_number">-->
                    <a href="javascript:;">
					<i class="fa fa-list"></i>
					<span class="title">
					Punjab Council </span>
                    <span class="arrow <?php if($this->uri->segment(1)=='punjab_council_roll_number'){echo 'open';}?>"></span>
					</a>
                    <ul class="sub-menu">
						<?php
                        	if(@$myAccess[0]['enter_punjab_council_roll_no']==1):
						?>
                        <li class="<?php if($this->uri->segment(1)=='punjab_council_roll_number' && $this->uri->segment(2)==''){echo 'active';}?>">
							<a href="<?php echo site_url();?>/punjab_council_roll_number">
							<i class="fa fa-plus"></i>
							Enter Punjab Council Roll no</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
                        	if(@$myAccess[0]['enter_punjab_council_result']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='result'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/punjab_council_roll_number/result">
							<i class="icon-list"></i>
							Enter Punjab Council Result</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
                        	if(@$myAccess[0]['final_result_pharmacy_technician']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='final_result'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/punjab_council_roll_number/final_result">
							<i class="icon-list"></i>
							Final Result Pharmacy Technician</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
                        	if(@$myAccess[0]['add_council_fee']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='add_council_fee'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/punjab_council_roll_number/add_council_fee">
							<i class="fa fa-money"></i>
							Add Council Fee</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
                        	if(@$myAccess[0]['next_exam_status']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='appear_in_next_exam'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/punjab_council_roll_number/appear_in_next_exam">
							<i class="fa fa-list"></i>
							Next Exam Status</a>
						</li>
                        <?php
                        	endif;
						?>
					</ul>
				</li>
                <?php
                	endif;
				?>
                
                <?php
					if(@$myAccess[0]['next_council_admission_access']==1):
				?>
                <li class="start <?php if($this->uri->segment(1)=='next_council_admissions'){echo 'active';}?>">
					<a href="<?php echo site_url();?>/next_council_admissions">
					<i class="fa fa-users"></i>
					<span class="title">
					Next Council Admissions </span>
                    <span class="selected"></span>
					</a>
				</li>
                <?php
                	endif;
				?>
                
                <?php
					if(@$myAccess[0]['download_documents']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='downloads'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-download"></i>
					<span class="title">Download Documents</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='downloads'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<li class="<?php if($this->uri->segment(1)=='add_download'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/downloads/add_download">
							<i class="icon-plus"></i>
							Add Download</a>
						</li>
						<li class="<?php if($this->uri->segment(2)=='all_downloads'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/downloads/all_downloads">
							<i class="icon-list"></i>
							All Downloads</a>
						</li>
					</ul>
				</li>
                <?php
                	endif;
				?>
				<?php
					if(@$myAccess[0]['course_management_access']==1):
				?>
				<li class="<?php if($this->uri->segment(1)=='courses' || $this->uri->segment(1)=='subjects' || $this->uri->segment(1)=='chapters' || $this->uri->segment(1)=='topics'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="icon-map"></i>
					<span class="title">Courses Management</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='courses' || $this->uri->segment(1)=='subjects' || $this->uri->segment(1)=='chapters' || $this->uri->segment(1)=='topics'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['course_management_add_course']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='add_course'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/courses/add_course">
							<i class="icon-plus"></i>
							Add Course</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['course_management_all_course']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_courses'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/courses/all_courses">
							<i class="icon-plus"></i>
							All Courses</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['course_management_add_subject']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='add_subject'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/subjects/add_subject">
							<i class="icon-plus"></i>
							Add Subject</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['course_management_all_subject']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_subjects'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/subjects/all_subjects">
							<i class="icon-list"></i>
							All Subjects</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['course_management_add_chapter']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='add_chapter'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/chapters/add_chapter">
							<i class="icon-plus"></i>
							Add Chapter</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['course_management_all_chapter']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_chapters'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/chapters/all_chapters">
							<i class="icon-list"></i>
							All Chapters</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['course_management_add_topic']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='add_topic'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/topics/add_topic">
							<i class="icon-plus"></i>
							Add Topic</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['course_management_all_topic']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_topics'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/topics/all_topics">
							<i class="icon-list"></i>
							All Topics</a>
						</li>
						<?php
							endif;
						?>
						<!--<li class="<?php if($this->uri->segment(2)=='all_classes'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/courses/add_syllabus">
							<i class="icon-plus"></i>
							Add Syllabus</a>
						</li>-->
					</ul>
				</li>
				<?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['test_engine_sidebar']==1):
				?>
				<li class="<?php if($this->uri->segment(1)=='test_engine'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-cogs"></i>
					<span class="title">Test Engine</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='test_engine'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['test_engine_add_practical_books']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='subjects'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/test_engine/subjects">
							<i class="icon-plus"></i>
							Add Practical &amp; Books</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['test_engine_view_question']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='topics'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/test_engine/topics">
							<i class="icon-plus"></i>
							Add Questions</a>
						</li>
                        <li class="<?php if($this->uri->segment(2)=='upload'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/test_engine/upload">
							<i class="fa fa-image"></i>
							Upload Image</a>
						</li>
						<?php
							endif;
						?>
					</ul>
				</li>
                <?php
                	endif;
				?>
				<?php
					if(@$myAccess[0]['papers_results_sidebar']==1):
				?>
				<li class="<?php if($this->uri->segment(1)=='collegepapers'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-list"></i>
					<span class="title">Papers &amp; Results</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='collegepapers'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['papers_results_add_paper']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='add_paper'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/collegepapers/add_paper">
							<i class="icon-plus"></i>
							Add Paper</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['papers_results_all_paper']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_paper'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/collegepapers/all_paper">
							<i class="icon-list"></i>
							All Papers</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['papers_results_all_paper']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='student_results'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/collegepapers/student_results">
							<i class="icon-list"></i>
							Student Results</a>
						</li>
						<?php
							endif;
						?>
					</ul>
				</li>
				<?php
                	endif;
				?>
                <?php
					if(@$myAccess[0]['hr_sidebar']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='hr'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-users"></i>
					<span class="title">HR</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='hr'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['hr_add_interview']==1):
						?>
                        <li class="<?php if($this->uri->segment(2)=='add_interview'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/hr/add_interview">
							<i class="icon-plus"></i>
							Add Interview</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
							if(@$myAccess[0]['hr_all_interview']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='subjects'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/hr/all_interviews">
							<i class="fa fa-list"></i>
							All Interview</a>
						</li>
                        <?php
                        	endif;
						?>
					</ul>
				</li>
                <?php
                	endif;
				?>
                
                <?php
					if(@$myAccess[0]['documents_access']==1):
				?>
                <li class="<?php if($this->uri->segment(1)=='documents'){echo 'active';}?>">
                    <a href="javascript:;">
					<i class="fa fa-list"></i>
					<span class="title">
					Documents </span>
                    <span class="arrow <?php if($this->uri->segment(1)=='documents'){echo 'open';}?>"></span>
					</a>
                    <ul class="sub-menu">
						<?php
							if(@$myAccess[0]['documents_diploma']==1):
						?>
                        <li class="<?php if($this->uri->segment(1)=='documents' && $this->uri->segment(2)=='diploma_documents'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/documents/diploma_documents">
							<i class="fa fa-list"></i>
							Dimploma Documents</a>
						</li>
                        <?php
                        	endif;
						?>
                        <?php
							if(@$myAccess[0]['documents_students']==1):
						?>
						<li class="<?php if($this->uri->segment(1)=='documents' && $this->uri->segment(2)=='students_documents'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/documents/students_documents">
							<i class="fa fa-list"></i>
							Students Documents</a>
						</li>
                        <?php
                        	endif;
						?>
					</ul>
				</li>
                <?php
                	endif;
				?>
                
				<?php
					if(@$myAccess[0]['reminders_sidebar']==1):
				?>
				<li class="<?php if($this->uri->segment(1)=='reminders'){echo 'active';}?>">
					<a href="javascript:;">
					<i class="fa fa-users"></i>
					<span class="title">Reminders</span>
					<span class="selected"></span>
					<span class="arrow <?php if($this->uri->segment(1)=='reminders'){echo 'open';}?>"></span>
					</a>
					<ul class="sub-menu">
						<?php
							if(@$myAccess[0]['reminders_add_rules']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='add_reminder'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/reminders/add_reminder">
							<i class="icon-plus"></i>
							Add Reminder Rules</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['reminders_all_rules']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_reminders'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/reminders/all_reminders">
							<i class="icon-list"></i>
							All Reminders Rules</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['reminders_all_pending']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_pending_reminder'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/reminders/all_pending_reminder">
							<i class="icon-list"></i>
							All Pending Reminders</a>
						</li>
						<?php
							endif;
						?>
						<?php
							if(@$myAccess[0]['reminders_all_completed']==1):
						?>
						<li class="<?php if($this->uri->segment(2)=='all_completed_reminder'){echo 'active';}?>">
							<a href="<?php echo site_url();?>/reminders/all_completed_reminder">
							<i class="icon-list"></i>
							All Completed Reminders</a>
						</li>
						<?php
							endif;
						?>
					</ul>
				</li>
				<?php
					endif;
				?>
			</ul>
            <?php
				endif;
			?>
			<!-- END SIDEBAR MENU -->
		</div>
	</div>
	<!-- END SIDEBAR -->
