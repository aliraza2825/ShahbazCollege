
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <!--<h3 class="page-title">
        All Teachers <small>Here you can find all teachers</small>
        </h3>-->
        <!-- BEGIN DASHBOARD STATS -->
        <!-- END DASHBOARD STATS -->
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
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i> Access
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/access">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Select</label>
                                            <div class="col-md-9 radio-list">
                                                <label class="radio-inline">
                                                    <input type="radio" name="select" class="selection" id="optionsRadios1" value="by_user" checked> By User </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="select" class="selection" id="optionsRadios2" value="by_designation"> By Designation </label>
                                            </div>
                                        </div>
                                        <div class="access_section by_user">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Select Campus <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control user_campus_id" name="user_campus_id" required>
                                                        <option>Select Campus</option>
                                                        <?php
                                                        foreach($campuses as $campus):
                                                            ?>
                                                            <option value="<?php echo $campus['campus_id'];?>" <?php if(@$this->input->post('user_campus_id')==$campus['campus_id']){echo 'selected';}?>><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                        endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Select User <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control campus_users" name="campus_user_id" required>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="access_section by_designation" style="display:none;">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Select Department <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control department_id" name="department_id">
                                                        <option>Select Department</option>
                                                        <?php
                                                        foreach($departments as $department):
                                                            ?>
                                                            <option value="<?php echo $department['department_id'];?>"><?php echo $department['department_name'];?></option>
                                                        <?php
                                                        endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Select Designation <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control designations" name="designation_id">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" class="btn green">Check</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php
                    if($this->input->post('campus_user_id') || $this->input->post('designation_id')):
                    ?>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data" action="<?php echo site_url();?>/access/add">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                        if(@$this->input->post('campus_user_id'))
                                        {
                                            $current_user =  $this->db->get_where('users',array('user_id'=>$this->input->post('campus_user_id')))->result_array();
                                            echo '<h2 style="text-align:center">User Access ('.$current_user[0]['first_name'].' '.$current_user[0]['last_name'].')</h2>';
                                        }else{
                                            $current_user =  $this->db->join('departments','departments.department_id = designations.department_id')->get_where('designations',array('designation_id'=>$this->input->post('designation_id')))->result_array();
                                            echo '<h2 style="text-align:center">User Access ('.$current_user[0]['department_name'].'-'.$current_user[0]['designation_name'].')</h2>';
                                        }
                                        ?>

                                        <hr />

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Campuses</strong> <span class="required">*</span></label>
                                            <div class="col-md-11">
                                                <select class="form-control select2 select2_sample1"  name="campus_ids[]" multiple>
                                                    <?php
                                                    foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if(in_array($campus['campus_id'], explode(',',@$access_values[0]['campus_ids']))){echo 'selected';}?>>
                                                            <?php echo $campus['campus_name'];?>
                                                        </option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                                <!--<span class="help-inline"></span>-->
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Dashboard</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="dashboard_total_student_box" value="1" <?php if(@$access_values[0]['dashboard_total_student_box']!=NULL){echo 'checked';}?> /> Total Students Box </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="dashboard_total_teacher_box" value="1" <?php if(@$access_values[0]['dashboard_total_teacher_box']!=NULL){echo 'checked';}?> /> Total Teachers Box </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="dashboard_new_admission" value="1" <?php if(@$access_values[0]['dashboard_new_admission']!=NULL){echo 'checked';}?> /> New Admissions Box </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox4" name="dashboard_month_earning" value="1" <?php if(@$access_values[0]['dashboard_month_earning']!=NULL){echo 'checked';}?> /> Monthly Earning Box </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox5" name="dashboard_month_expense" value="1" <?php if(@$access_values[0]['dashboard_month_expense']!=NULL){echo 'checked';}?> /> Monthly Expense Box </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox6" name="dashboard_month_profit" value="1" <?php if(@$access_values[0]['dashboard_month_profit']!=NULL){echo 'checked';}?> /> Monthly Profit Box </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox7" name="dashboard_fee_status" value="1" <?php if(@$access_values[0]['dashboard_fee_status']!=NULL){echo 'checked';}?> /> Fees Status </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox8" name="dashboard_classes_status" value="1" <?php if(@$access_values[0]['dashboard_classes_status']!=NULL){echo 'checked';}?> /> All Classes Status </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox9" name="dashboard_update_payment_box" value="1" <?php if(@$access_values[0]['dashboard_update_payment_box']!=NULL){echo 'checked';}?> /> Update Fee Requests </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox9" name="dashboard_update_reversal_payment_box" value="1" <?php if(@$access_values[0]['dashboard_update_reversal_payment_box']!=NULL){echo 'checked';}?> /> Update Fee Reversal Requests </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox9" name="dashboard_update_discount_box" value="1" <?php if(@$access_values[0]['dashboard_update_discount_box']!=NULL){echo 'checked';}?> /> Fee Discount Requests </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox10" name="dashboard_update_student_box" value="1" <?php if(@$access_values[0]['dashboard_update_student_box']!=NULL){echo 'checked';}?> /> Update Students Requests </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox11" name="dashboard_check_student_box" value="1" <?php if(@$access_values[0]['dashboard_check_student_box']!=NULL){echo 'checked';}?> /> Check Students Record (Any Query) </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox12" name="dashboard_campus_status_box" value="1" <?php if(@$access_values[0]['dashboard_campus_status_box']!=NULL){echo 'checked';}?> /> Campus Status </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox13" name="dashboard_new_admisssion_entries_box" value="1" <?php if(@$access_values[0]['dashboard_new_admisssion_entries_box']!=NULL){echo 'checked';}?> /> New Admission Entries </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox14" name="dashboard_new_expense_entries_box" value="1" <?php if(@$access_values[0]['dashboard_new_expense_entries_box']!=NULL){echo 'checked';}?> /> New Expense Entries </label>
                                                <!--<label class="checkbox-inline">
                                                        <input type="checkbox" id="inlineCheckbox15" name="dashboard_students_due_fees" value="1" <?php //if(@$access_values[0]['dashboard_students_due_fees']!=NULL){echo 'checked';}?> /> Students Due Fees </label>-->
                                                <!--<label class="checkbox-inline">
                                                        <input type="checkbox" id="inlineCheckbox16" name="dashboard_students_due_fees_status" value="1" <?php //if(@$access_values[0]['dashboard_students_due_fees_status']!=NULL){echo 'checked';}?> /> Students Due Fees Status Clear </label>-->
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox17" name="dashboard_reminders_status" value="1" <?php if(@$access_values[0]['dashboard_reminders_status']!=NULL){echo 'checked';}?> /> Reminder Status </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox18" name="dashboard_test_engine_questions" value="1" <?php if(@$access_values[0]['dashboard_test_engine_questions']!=NULL){echo 'checked';}?> /> Test Engine Questions </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox19" name="dashboard_uncheck_assignment" value="1" <?php if(@$access_values[0]['dashboard_uncheck_assignment']!=NULL){echo 'checked';}?> /> Uncheck Assignments </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="student_struck_off_list" value="1" <?php if(@$access_values[0]['student_struck_off_list']!=NULL){echo 'checked';}?> /> Struck of Students in Inquiry </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox5" name="student_delete" value="1" <?php if(@$access_values[0]['student_delete']!=NULL){echo 'checked';}?> /> Final Struck of Students </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox5" name="dashboard_students_fees_reversal" value="1" <?php if(@$access_values[0]['dashboard_students_fees_reversal']!=NULL){echo 'checked';}?> /> Fee Reversal Request </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox5" name="how_to_use" value="1" <?php if(@$access_values[0]['how_to_use']!=NULL){echo 'checked';}?> /> How To Use </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Online Application</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="online_application_access" value="1" <?php if(@$access_values[0]['online_application_access']!=NULL){echo 'checked';}?> /> Online Application Access </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="online_application_new_admissions" value="1" <?php if(@$access_values[0]['online_application_new_admissions']!=NULL){echo 'checked';}?> /> New Applications </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="online_application_checked_admissions" value="1" <?php if(@$access_values[0]['online_application_checked_admissions']!=NULL){echo 'checked';}?> /> Checked Applications </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox4" name="online_application_all" value="1" <?php if(@$access_values[0]['online_application_all']!=NULL){echo 'checked';}?> /> All Applications </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox4" name="facebook_leads" value="1" <?php if(@$access_values[0]['facebook_leads']!=NULL){echo 'checked';}?> /> Facebook Leads Upload </label>
                                                <label class="checkbox-inline">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Accounts</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="accounts_sidebar" value="1" <?php if(@$access_values[0]['accounts_sidebar']!=NULL){echo 'checked';}?> /> Accounts Access </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="account_details" value="1" <?php if(@$access_values[0]['account_details']!=NULL){echo 'checked';}?> /> Account Details </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="profit_distribution" value="1" <?php if(@$access_values[0]['profit_distribution']!=NULL){echo 'checked';}?> /> Profit Distribution </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="campus_petty_cash" value="1" <?php if(@$access_values[0]['campus_petty_cash']!=NULL){echo 'checked';}?> /> Campus Petty Cash </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="advance_system" value="1" <?php if(@$access_values[0]['advance_system']!=NULL){echo 'checked';}?> /> Advance System </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="dailyclosing" value="1" <?php if(@$access_values[0]['dailyclosing']!=NULL){echo 'checked';}?> /> Daily Closing Cash</label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="dailybankclosing" value="1" <?php if(@$access_values[0]['dailybankclosing']!=NULL){echo 'checked';}?> /> Daily Closing Bank</label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="accounts" value="1" <?php if(@$access_values[0]['accounts']!=NULL){echo 'checked';}?> /> Accounts View </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="closing_reconcile" value="1" <?php if(@$access_values[0]['closing_reconcile']!=NULL){echo 'checked';}?> /> Closing Reconciliation </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="closing_conciliation_edit" value="1" <?php if(@$access_values[0]['closing_conciliation_edit']!=NULL){echo 'checked';}?> /> Closing Reconciliation Edit Amount Popup </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="bank_reconciliation" value="1" <?php if(@$access_values[0]['bank_reconciliation']!=NULL){echo 'checked';}?> /> Bank Reconciliation</label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="view_campus_closings" value="1" <?php if(@$access_values[0]['view_campus_closings']!=NULL){echo 'checked';}?> /> View Campus Closings</label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="closing_amount_edit" value="1" <?php if(@$access_values[0]['closing_amount_edit']!=NULL){echo 'checked';}?> /> Accounts Closings Amount Edit</label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="closing_coo" value="1" <?php if(@$access_values[0]['closing_coo']!=NULL){echo 'checked';}?> /> Coo Daily Closing</label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="misc_income" value="1" <?php if(@$access_values[0]['misc_income']!=NULL){echo 'checked';}?> /> Miscellaneous Income</label>
                                                <?php

                                                $this->db->select('*,closing_persons.id as id');
                                                $this->db->from('closing_persons');
                                                $this->db->join('campuses','campuses.campus_id = closing_persons.campus_id','left');
                                                $this->db->join('users','users.user_id = closing_persons.user_id','left');
                                                $this->db->where('closing_persons.active_status = 1');
                                                $this->db->order_by('closing_persons.id', 'DESC');
                                                $closings = $this->db->get()->result_array();

                                                if(count($closings) > 0):
                                                    ?>
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label"> Select View Daily Closings <span class="required">*</span></label>
                                                        <div class="col-md-5">
                                                            <select class="form-control select2" id="select2_sample10" name="campus_closing_ids[]" multiple>
                                                                <?php
                                                                foreach($closings as $campus):
                                                                    ?>
                                                                    <option value="<?php echo $campus['id'];?>" <?php if(in_array($campus['id'], explode(',',@$access_values[0]['campus_closing_ids']))){echo 'selected';}?>>
                                                                        <?php echo $campus['campus_name'].' '.$campus['first_name'].' '.$campus['last_name'];?>
                                                                    </option>
                                                                <?php
                                                                endforeach;
                                                                ?>
                                                            </select>
                                                            <!--<span class="help-inline"></span>-->
                                                        </div>
                                                    </div>

                                                <?php endif; ?>
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Petty Cash</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="pettycash_sidebar" value="1" <?php if(@$access_values[0]['pettycash_sidebar']!=NULL){echo 'checked';}?> /> Petty Cash Access </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="cash_request" value="1" <?php if(@$access_values[0]['cash_request']!=NULL){echo 'checked';}?> /> Petty Cash Request </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="cash_approval" value="1" <?php if(@$access_values[0]['cash_approval']!=NULL){echo 'checked';}?> /> Petty Cash Request Approval </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="is_carrier" value="1" <?php if(@$access_values[0]['is_carrier']!=NULL){echo 'checked';}?> /> Petty Cash Carrier </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="add_pettycash" value="1" <?php if(@$access_values[0]['add_pettycash']!=NULL){echo 'checked';}?> /> Add Petty Cash </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="change_pettycash" value="1" <?php if(@$access_values[0]['change_pettycash']!=NULL){echo 'checked';}?> /> Change Petty Cash </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="pettycash_funds_trasfer" value="1" <?php if(@$access_values[0]['pettycash_funds_trasfer']!=NULL){echo 'checked';}?> /> Funds Trasfer from Petty Cash </label>


                                                <?php

                                                $this->db->select('*');
                                                $this->db->from('petty_cash_college_wise');
                                                $this->db->join('campuses','campuses.campus_id = petty_cash_college_wise.campus_id','left');
                                                $this->db->join('users','users.user_id = petty_cash_college_wise.assign_to','left');
                                                $this->db->join('designations','designations.designation_id = users.designation_id','left');
                                                $this->db->where ('petty_cash_college_wise.petty_status','1');
                                                $pett = $this->db->get()->result_array();

                                                if(count($pett) > 0):
                                                    ?>
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label"> Select View Petty Cash <span class="required">*</span></label>
                                                        <div class="col-md-5">
                                                            <select class="form-control select2" id="select2_sample1" name="petty_cash_users[]" multiple>
                                                                <?php
                                                                foreach($pett as $campus):
                                                                    ?>
                                                                    <option value="<?php echo $campus['id'];?>" <?php if(in_array($campus['id'], explode(',',@$access_values[0]['petty_cash_users']))){echo 'selected';}?>>
                                                                        <?php echo $campus['first_name'].' '.$campus['last_name'];?>
                                                                    </option>
                                                                <?php
                                                                endforeach;
                                                                ?>
                                                            </select>
                                                            <!--<span class="help-inline"></span>-->
                                                        </div>
                                                    </div>

                                                <?php endif; ?>


                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Attendence</strong></label>
                                            <div class="col-md-11 checkbox-list">

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="attendence_sidebar" value="1" <?php if(@$access_values[0]['attendence_sidebar']!=NULL){echo 'checked';}?> /> Attendence Access </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="attendence_add_attendence" value="1" <?php if(@$access_values[0]['attendence_add_attendence']!=NULL){echo 'checked';}?> /> Add Attendence </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="attendence_all_attendence" value="1" <?php if(@$access_values[0]['attendence_all_attendence']!=NULL){echo 'checked';}?> /> All Attendence </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="attendance_mobile_report" value="1" <?php if(@$access_values[0]['attendance_mobile_report']!=NULL){echo 'checked';}?> /> Student Attendance Mobile </label>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label"> Select User Can add Attendance for  <span class="required">*</span></label>
                                                    <div class="col-md-5">
                                                        <select class="form-control select2" id="select2_sampletype" name="attendence_add_types[]" multiple>
                                                            <option value="Staff"
                                                                <?php
                                                                if (in_array("Staff", explode(',', @$access_values[0]['attendence_add_types']))) {
                                                                    echo 'selected';
                                                                }
                                                                ?>
                                                            >Staff</option>
                                                            <option value="Student"
                                                                <?php
                                                                if (in_array("Student", explode(',', @$access_values[0]['attendence_add_types']))) {
                                                                    echo 'selected';
                                                                }
                                                                ?>
                                                            >Student</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Human Resource</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="define_allownces" value="1" <?php if(@$access_values[0]['define_allownces']!=NULL){echo 'checked';}?> /> Add/Update Allownces </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="salary" value="1" <?php if(@$access_values[0]['salary']!=NULL){echo 'checked';}?> /> Generate Salaries </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="loans" value="1" <?php if(@$access_values[0]['loan_approval']!=NULL){echo 'checked';}?> /> Loan/Advance Approval </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="loan_approval_accounts" value="1" <?php if(@$access_values[0]['loan_approval_accounts']!=NULL){echo 'checked';}?> /> Accounts Loan/Advance Approval </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="leave_approval" value="1" <?php if(@$access_values[0]['leave_approval']!=NULL){echo 'checked';}?> /> Leaves Approval </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="apply_loan" value="1" <?php if(@$access_values[0]['apply_loan']!=NULL){echo 'checked';}?> /> Apply Staff Loan </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="payroll_statutory_rules" value="1" <?php if(@$access_values[0]['payroll_statutory_rules']!=NULL){echo 'checked';}?> /> Statutory Rules </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="payroll_income_tax_rules" value="1" <?php if(@$access_values[0]['payroll_income_tax_rules']!=NULL){echo 'checked';}?> /> Income Tax Rules </label>


                                            </div>
                                        </div>



                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Department</strong></label>
                                            <div class="col-md-11 checkbox-list">

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="department_sidebar" value="1" <?php if(@$access_values[0]['department_sidebar']!=NULL){echo 'checked';}?> /> Department Access </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="department_add_department" value="1" <?php if(@$access_values[0]['department_add_department']!=NULL){echo 'checked';}?> /> Add Department </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="department_all_department" value="1" <?php if(@$access_values[0]['department_all_department']!=NULL){echo 'checked';}?> /> All Departments </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="department_edit_department" value="1" <?php if(@$access_values[0]['department_edit_department']!=NULL){echo 'checked';}?> /> Edit Department </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="department_delete_department" value="1" <?php if(@$access_values[0]['department_delete_department']!=NULL){echo 'checked';}?> /> Delete Department </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Designation</strong></label>
                                            <div class="col-md-11 checkbox-list">

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="designation_sidebar" value="1" <?php if(@$access_values[0]['designation_sidebar']!=NULL){echo 'checked';}?> /> Designation Access </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="designation_add_designation" value="1" <?php if(@$access_values[0]['designation_add_designation']!=NULL){echo 'checked';}?> /> Add Designation </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="designation_all_designation" value="1" <?php if(@$access_values[0]['designation_all_designation']!=NULL){echo 'checked';}?> /> All Designation </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="designation_edit_designation" value="1" <?php if(@$access_values[0]['designation_edit_designation']!=NULL){echo 'checked';}?> /> Edit Designation </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="designation_delete_designation" value="1" <?php if(@$access_values[0]['designation_delete_designation']!=NULL){echo 'checked';}?> /> Delete Designation </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Staff Type</strong></label>
                                            <div class="col-md-11 checkbox-list">

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="staff_type_sidebar" value="1" <?php if(@$access_values[0]['staff_type_sidebar']!=NULL){echo 'checked';}?> /> Staff Type Access </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="staff_type_add_staff_type" value="1" <?php if(@$access_values[0]['staff_type_add_staff_type']!=NULL){echo 'checked';}?> /> Add Staff Type </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="staff_type_all_staff_type" value="1" <?php if(@$access_values[0]['staff_type_all_staff_type']!=NULL){echo 'checked';}?> /> All Staff Type </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="staff_type_edit_staff_type" value="1" <?php if(@$access_values[0]['staff_type_edit_staff_type']!=NULL){echo 'checked';}?> /> Edit Staff Type </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="staff_type_delete_staff_type" value="1" <?php if(@$access_values[0]['staff_type_delete_staff_type']!=NULL){echo 'checked';}?> /> Delete Staff Type </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Staff</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="staff_sidebar" value="1" <?php if(@$access_values[0]['staff_sidebar']!=NULL){echo 'checked';}?> /> Staff Access </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="staff_add" value="1" <?php if(@$access_values[0]['staff_add']!=NULL){echo 'checked';}?> /> Add Staff </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="staff_all" value="1" <?php if(@$access_values[0]['staff_all']!=NULL){echo 'checked';}?> /> Check All Staff </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox4" name="staff_edit" value="1" <?php if(@$access_values[0]['staff_edit']!=NULL){echo 'checked';}?> /> Edit Staff </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox5" name="staff_upload_documents" value="1" <?php if(@$access_values[0]['staff_upload_documents']!=NULL){echo 'checked';}?> /> Upload Staff Documents </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox6" name="staff_delete" value="1" <?php if(@$access_values[0]['staff_delete']!=NULL){echo 'checked';}?> /> Delete Staff </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox7" name="staff_attendence" value="1" <?php if(@$access_values[0]['staff_attendence']!=NULL){echo 'checked';}?> /> Staff Attendence </label>
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Incentive Management</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="recovery_portal" value="1" <?php if(@$access_values[0]['recovery_portal']!=NULL){echo 'checked';}?> /> Incentive Portal </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="all_users_recovery" value="1" <?php if(@$access_values[0]['all_users_recovery']!=NULL){echo 'checked';}?> /> All Users Incentive Portal</label>

                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Classes</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="class_sidebar" value="1" <?php if(@$access_values[0]['class_sidebar']!=NULL){echo 'checked';}?> /> Class Access </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="class_add" value="1" <?php if(@$access_values[0]['class_add']!=NULL){echo 'checked';}?> /> Add Class </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="class_all" value="1" <?php if(@$access_values[0]['class_all']!=NULL){echo 'checked';}?> /> Check All Class </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox4" name="class_edit" value="1" <?php if(@$access_values[0]['class_edit']!=NULL){echo 'checked';}?> /> Edit Class </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox5" name="class_delete" value="1" <?php if(@$access_values[0]['class_delete']!=NULL){echo 'checked';}?> /> Delete Class </label>
                                            </div>
                                        </div>

                                        <!--<div class="form-group">
                                                <label class="col-md-1 control-label"><strong>Subject</strong></label>
                                                <div class="col-md-11 checkbox-list">
                                                    <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="subject_sidebar" value="1" <?php if(@$access_values[0]['subject_sidebar']!=NULL){echo 'checked';}?> /> Subject Access </label>
                                                    <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="subject_add" value="1" <?php if(@$access_values[0]['subject_add']!=NULL){echo 'checked';}?> /> Subject Add </label>
                                                    <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="subject_all" value="1" <?php if(@$access_values[0]['subject_all']!=NULL){echo 'checked';}?> /> Check All Subject </label>
                                                    <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox4" name="subject_edit" value="1" <?php if(@$access_values[0]['subject_edit']!=NULL){echo 'checked';}?> /> Edit Subject </label>
                                                    <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox5" name="subject_delete" value="1" <?php if(@$access_values[0]['subject_delete']!=NULL){echo 'checked';}?> /> Delete Subject </label>
                                                </div>
                                            </div>-->

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Reports</strong></label>
                                            <div class="col-md-11 checkbox-list">

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="reports_sidebar" value="1" <?php if(@$access_values[0]['reports_sidebar']!=NULL){echo 'checked';}?> /> Reports Access </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="reports_student_fee_problem" value="1" <?php if(@$access_values[0]['reports_student_fee_problem']!=NULL){echo 'checked';}?> /> Students Fee Problem </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="all_struckofstudent_report" value="1" <?php if(@$access_values[0]['all_struckofstudent_report']!=NULL){echo 'checked';}?> /> Struck of Students Report </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="reports_discount_report" value="1" <?php if(@$access_values[0]['reports_discount_report']!=NULL){echo 'checked';}?> /> Discount Report </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="session_students_mobile_report" value="1" <?php if(@$access_values[0]['session_students_mobile_report']!=NULL){echo 'checked';}?> /> Students Session Wise Mobile Report </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="agent_view_statement" value="1" <?php if(@$access_values[0]['agent_view_statement']!=NULL){echo 'checked';}?> /> Agent View Statement </label>
                                                    
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="agent_view_statement_coo" value="1" <?php if(@$access_values[0]['agent_view_statement_coo']!=NULL){echo 'checked';}?> /> Agent View Statement COO</label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="student_backup_report" value="1" <?php if(@$access_values[0]['student_backup_report']!=NULL){echo 'checked';}?> /> Students Backup Report </label>

                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Student</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="student_sidebar" value="1" <?php if(@$access_values[0]['student_sidebar']!=NULL){echo 'checked';}?> /> Student Access </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="student_add" value="1" <?php if(@$access_values[0]['student_add']!=NULL){echo 'checked';}?> /> Student Add </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="student_all" value="1" <?php if(@$access_values[0]['student_all']!=NULL){echo 'checked';}?> /> Check All Student </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox4" name="student_edit" value="1" <?php if(@$access_values[0]['student_edit']!=NULL){echo 'checked';}?> /> Edit Student </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox5" name="council_list_report" value="1" <?php if(@$access_values[0]['council_list_report']!=NULL){echo 'checked';}?> /> Council List Report </label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox6" name="student_upload_documents" value="1" <?php if(@$access_values[0]['student_upload_documents']!=NULL){echo 'checked';}?> /> Student Upload Documents </label>
                                                <br class="clear" />
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox7" name="student_payments" value="1" <?php if(@$access_values[0]['student_payments']!=NULL){echo 'checked';}?> /> Student Payment </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox7" name="installment_date" value="1" <?php if(@$access_values[0]['installment_date']!=NULL){echo 'checked';}?> /> Student Installment Date Access </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox8" name="student_payment_reset" value="1" <?php if(@$access_values[0]['student_payment_reset']!=NULL){echo 'checked';}?> /> Reset Payment </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox9" name="student_payment_edit" value="1" <?php if(@$access_values[0]['student_payment_edit']!=NULL){echo 'checked';}?> /> Student Payment Edit </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox9" name="student_payment_delete" value="1" <?php if(@$access_values[0]['student_payment_delete']!=NULL){echo 'checked';}?> /> Student Payment Delete </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="update_fee_submission" value="1" <?php if(@$access_values[0]['update_fee_submission']!=NULL){echo 'checked';}?> /> Update Fee Submission Bank</label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="fine_remove" value="1" <?php if(@$access_values[0]['fine_remove']!=NULL){echo 'checked';}?> /> Remove Fine Access</label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="extra_fee_access" value="1" <?php if(@$access_values[0]['extra_fee_access']!=NULL){echo 'checked';}?> /> Add Extra Fee</label>

                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox9" name="student_college_card" value="1" <?php if(@$access_values[0]['student_college_card']!=NULL){echo 'checked';}?> /> Student College Card </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox10" name="student_issue_refund" value="1" <?php if(@$access_values[0]['student_issue_refund']!=NULL){echo 'checked';}?> /> Student Issue Refund </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox11" name="can_student_struckof" value="1" <?php if(@$access_values[0]['can_student_struckof']!=NULL){echo 'checked';}?> /> Can Struck of / Freeze Student </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="discount_reversal" value="1" <?php if(@$access_values[0]['discount_reversal']!=NULL){echo 'checked';}?> /> Discount Reversal Access </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="delete_users_payment" value="1" <?php if(@$access_values[0]['delete_users_payment']!=NULL){echo 'checked';}?> /> Student Payment Delete User Access </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="fee_by_cash" value="1" <?php if(@$access_values[0]['fee_by_cash']!=NULL){echo 'checked';}?> /> Receive By Cash  </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="fee_by_bank" value="1" <?php if(@$access_values[0]['fee_by_bank']!=NULL){echo 'checked';}?> /> Receive By Bank  </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="fee_by_paypro" value="1" <?php if(@$access_values[0]['fee_by_paypro']!=NULL){echo 'checked';}?> /> Receive By PayPro  </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="receipt_book" value="1" <?php if(@$access_values[0]['receipt_book']!=NULL){echo 'checked';}?> /> Receive By Receipt Book  </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="computer_challan" value="1" <?php if(@$access_values[0]['computer_challan']!=NULL){echo 'checked';}?> /> Receive By Computer Challan  </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="archived_students" value="1" <?php if(@$access_values[0]['archived_students']!=NULL){echo 'checked';}?> /> Archeived Students  </label>
                                                    <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="change_exam_no_in_payments" value="1" <?php if(@$access_values[0]['change_exam_no_in_payments']!=NULL){echo 'checked';}?> /> Change Exam No in Payments  </label>

                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Contractor</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="contractor_sidebar" value="1" <?php if(@$access_values[0]['contractor_sidebar']!=NULL){echo 'checked';}?> /> Contractor Access </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="contractor_add" value="1" <?php if(@$access_values[0]['contractor_add']!=NULL){echo 'checked';}?> /> Add Contractor </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="contractor_all" value="1" <?php if(@$access_values[0]['contractor_all']!=NULL){echo 'checked';}?> /> Check All Contractor </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox4" name="contractor_edit" value="1" <?php if(@$access_values[0]['contractor_edit']!=NULL){echo 'checked';}?> /> Edit Contractor </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox5" name="contractor_delete" value="1" <?php if(@$access_values[0]['contractor_delete']!=NULL){echo 'checked';}?> /> Delete Contractor </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox6" name="contractor_payments" value="1" <?php if(@$access_values[0]['contractor_payments']!=NULL){echo 'checked';}?> /> Contractor Payment </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox7" name="contractor_payment_reset" value="1" <?php if(@$access_values[0]['contractor_payment_reset']!=NULL){echo 'checked';}?> /> Reset Payment </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox8" name="contract_sidebar" value="1" <?php if(@$access_values[0]['contract_sidebar']!=NULL){echo 'checked';}?> /> Contract Access </label>

                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Visitors</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="visitor_sidebar" value="1" <?php if(@$access_values[0]['visitor_sidebar']!=NULL){echo 'checked';}?> /> Visitor Access </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="visitor_add" value="1" <?php if(@$access_values[0]['visitor_add']!=NULL){echo 'checked';}?> /> Add Visitor </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox3" name="visitor_all" value="1" <?php if(@$access_values[0]['visitor_all']!=NULL){echo 'checked';}?> /> Check All Visitor </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox4" name="visitor_edit" value="1" <?php if(@$access_values[0]['visitor_edit']!=NULL){echo 'checked';}?> /> Edit Visitor </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox5" name="visitor_delete" value="1" <?php if(@$access_values[0]['visitor_delete']!=NULL){echo 'checked';}?> /> Delete Visitor </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>expense</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="expense_sidebar" value="1" <?php if(@$access_values[0]['expense_sidebar']!=NULL){echo 'checked';}?> /> Expense Access </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="expense_add" value="1" <?php if(@$access_values[0]['expense_add']!=NULL){echo 'checked';}?> /> Add Expense (Portal)</label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="expense_add_mobile" value="1" <?php if(@$access_values[0]['expense_add_mobile']!=NULL){echo 'checked';}?> /> Add Expense (Mobile)</label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="expense_advertisement_create" value="1" <?php if(@$access_values[0]['expense_advertisement_create']!=NULL){echo 'checked';}?> /> Add Advertisement Expense (Mobile)</label>
                                                <label class="checkbox-inline" style="background: #efb878;">
                                                    <input type="checkbox" id="inlineCheckbox3" name="expense_all" value="1" <?php if(@$access_values[0]['expense_all']!=NULL){echo 'checked';}?> /> Check All Expense </label>
                                                <label class="checkbox-inline" style="background: bisque;">
                                                    <input type="checkbox" style="background: bisque;" id="inlineCheckbox6" name="expense_view_user" value="1" <?php if(@$access_values[0]['expense_view_user']!=NULL){echo 'checked';}?> /> All User Expense View</label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox6" name="expense_approval" value="1" <?php if(@$access_values[0]['expense_approval']!=NULL){echo 'checked';}?> /> Expense First Approval/Edit (Mobile)</label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="expense_second_approval" value="1" <?php if(@$access_values[0]['expense_second_approval']!=NULL){echo 'checked';}?> /> Expense Second Approval+Reversal Approval (Mobile)</label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox2" name="expense_advertisement_approval" value="1" <?php if(@$access_values[0]['expense_advertisement_approval']!=NULL){echo 'checked';}?> /> Expense Advertisement Approval (Mobile)</label>
                                                <label class="checkbox-inline" style="background: bisque;">
                                                    <input type="checkbox"  id="inlineCheckbox6" name="expense_no_of_days" value="1" <?php if(@$access_values[0]['expense_no_of_days']!=NULL){echo 'checked';}?> /> All Time Expense View</label>


                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox4" name="expense_edit" value="1" <?php if(@$access_values[0]['expense_edit']!=NULL){echo 'checked';}?> /> Edit Expense </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox5" name="expense_delete" value="1" <?php if(@$access_values[0]['expense_delete']!=NULL){echo 'checked';}?> /> Delete Expense </label>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox6" name="expense_category" value="1" <?php if(@$access_values[0]['expense_category']!=NULL){echo 'checked';}?> /> Expense Category</label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-3 control-label"> Expense Campuses <span class="required">*</span></label>
                                            <div class="col-md-5">
                                                <select class="form-control select2" id="select2_sample6" name="expense_campus_ids[]" multiple>
                                                    <?php
                                                    foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if(in_array($campus['campus_id'], explode(',',@$access_values[0]['expense_campus_ids']))){echo 'selected';}?>>
                                                            <?php echo $campus['campus_name'];?>
                                                        </option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                                <!--<span class="help-inline"></span>-->
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-md-1 control-label"><strong>Purchaser</strong></label>
                                            <div class="col-md-11 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="is_purchaser" value="1" <?php if(@$access_values[0]['is_purchaser']!=NULL){echo 'checked';}?> /> Purchaser Access </label> </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Inventory</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="inventory" value="1" <?php if(@$access_values[0]['inventory']!=NULL){echo 'checked';}?> /> Inventory Access
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox2" name="add_vendor" value="1" <?php if(@$access_values[0]['add_vendor']!=NULL){echo 'checked';}?> /> Add Vendor
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox3" name="manage_vendor" value="1" <?php if(@$access_values[0]['manage_vendor']!=NULL){echo 'checked';}?> /> Manage Vendor
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox2" name="edit_vendor" value="1" <?php if(@$access_values[0]['edit_vendor']!=NULL){echo 'checked';}?> /> Edit Vendor
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox3" name="delete_vendor" value="1" <?php if(@$access_values[0]['delete_vendor']!=NULL){echo 'checked';}?> /> Delete Vendor
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox4" name="add_purchase_request" value="1" <?php if(@$access_values[0]['add_purchase_request']!=NULL){echo 'checked';}?> /> Add Product Request
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox5" name="all_purchase_request" value="1" <?php if(@$access_values[0]['all_purchase_request']!=NULL){echo 'checked';}?> /> All Product Requests
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox4" name="edit_purchase_request" value="1" <?php if(@$access_values[0]['edit_purchase_request']!=NULL){echo 'checked';}?> /> Edit Product Request
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox5" name="delete_purchase_request" value="1" <?php if(@$access_values[0]['delete_purchase_request']!=NULL){echo 'checked';}?> /> Delete Product Request
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox4" name="add_qoutation" value="1" <?php if(@$access_values[0]['add_qoutation']!=NULL){echo 'checked';}?> /> Add Qoutation
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox4" name="approve_qoutation" value="1" <?php if(@$access_values[0]['approve_qoutation']!=NULL){echo 'checked';}?> /> Approve Qoutation
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox4" name="purchase_orders" value="1" <?php if(@$access_values[0]['purchase_orders']!=NULL){echo 'checked';}?> /> Purchase Orders
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox4" name="grn_gate_approval" value="1" <?php if(@$access_values[0]['grn_gate_approval']!=NULL){echo 'checked';}?> /> GRN Gate Approval
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox4" name="grn_approval" value="1" <?php if(@$access_values[0]['grn_approval']!=NULL){echo 'checked';}?> /> GRN Approval
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox6" name="add_room" value="1" <?php if(@$access_values[0]['add_room']!=NULL){echo 'checked';}?> /> Add Room
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox7" name="all_room" value="1" <?php if(@$access_values[0]['all_room']!=NULL){echo 'checked';}?> /> All Room
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox6" name="edit_room" value="1" <?php if(@$access_values[0]['edit_room']!=NULL){echo 'checked';}?> /> Edit Room
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox7" name="delete_room" value="1" <?php if(@$access_values[0]['delete_room']!=NULL){echo 'checked';}?> /> Delete Room
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox8" name="add_subroom" value="1" <?php if(@$access_values[0]['add_subroom']!=NULL){echo 'checked';}?> /> Add SubRoom
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox9" name="all_subroom" value="1" <?php if(@$access_values[0]['all_subroom']!=NULL){echo 'checked';}?> /> All SubRoom
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox8" name="edit_subroom" value="1" <?php if(@$access_values[0]['edit_subroom']!=NULL){echo 'checked';}?> /> Edit SubRoom
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox9" name="delete_subroom" value="1" <?php if(@$access_values[0]['delete_subroom']!=NULL){echo 'checked';}?> /> Delete SubRoom
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox10" name="manage_product_names" value="1" <?php if(@$access_values[0]['manage_product_names']!=NULL){echo 'checked';}?> /> Manage Product Names
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox11" name="manage_document_names" value="1" <?php if(@$access_values[0]['manage_document_names']!=NULL){echo 'checked';}?> /> Manage Document Names
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox12" name="add_product" value="1" <?php if(@$access_values[0]['add_product']!=NULL){echo 'checked';}?> /> Add Product
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox13" name="all_product" value="1" <?php if(@$access_values[0]['all_product']!=NULL){echo 'checked';}?> /> All Products
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox14" name="add_product_issue_request" value="1" <?php if(@$access_values[0]['add_product_issue_request']!=NULL){echo 'checked';}?> /> Add Product Issue Request
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox15" name="all_product_issue_request" value="1" <?php if(@$access_values[0]['all_product_issue_request']!=NULL){echo 'checked';}?> /> All Product Issue Request
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox16" name="manage_gin" value="1" <?php if(@$access_values[0]['manage_gin']!=NULL){echo 'checked';}?> /> Manage GIN
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox17" name="manage_grn" value="1" <?php if(@$access_values[0]['manage_grn']!=NULL){echo 'checked';}?> /> Manage GRN
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox18" name="generate_qrs" value="1" <?php if(@$access_values[0]['generate_qrs']!=NULL){echo 'checked';}?> /> Manage QRs
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox18" name="product_return_request" value="1" <?php if(@$access_values[0]['product_return_request']!=NULL){echo 'checked';}?> /> Product Return Request
                                        </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox18" name="approve_product_return_request" value="1" <?php if(@$access_values[0]['approve_product_return_request']!=NULL){echo 'checked';}?> /> Approve Product Return Request
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Construction</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_sidebar" value="1" <?php if(@$access_values[0]['construction_sidebar']!=NULL){echo 'checked';}?> /> Construction Access</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_dashboard" value="1" <?php if(@$access_values[0]['construction_dashboard']!=NULL){echo 'checked';}?> /> Dashboard</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_projects" value="1" <?php if(@$access_values[0]['construction_projects']!=NULL){echo 'checked';}?> /> Projects</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_add_project" value="1" <?php if(@$access_values[0]['construction_add_project']!=NULL){echo 'checked';}?> /> Add Project</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_boq" value="1" <?php if(@$access_values[0]['construction_boq']!=NULL){echo 'checked';}?> /> BOQ / Estimate</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_add_boq" value="1" <?php if(@$access_values[0]['construction_add_boq']!=NULL){echo 'checked';}?> /> Add BOQ</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_work" value="1" <?php if(@$access_values[0]['construction_work']!=NULL){echo 'checked';}?> /> Site Work</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_issue_material" value="1" <?php if(@$access_values[0]['construction_issue_material']!=NULL){echo 'checked';}?> /> Issue Material</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_add_labour" value="1" <?php if(@$access_values[0]['construction_add_labour']!=NULL){echo 'checked';}?> /> Add Labour</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_labour_attendance" value="1" <?php if(@$access_values[0]['construction_labour_attendance']!=NULL){echo 'checked';}?> /> Labour Attendance</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_site_expense" value="1" <?php if(@$access_values[0]['construction_site_expense']!=NULL){echo 'checked';}?> /> Site Expense</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_equipment" value="1" <?php if(@$access_values[0]['construction_equipment']!=NULL){echo 'checked';}?> /> Equipment</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_progress" value="1" <?php if(@$access_values[0]['construction_progress']!=NULL){echo 'checked';}?> /> Progress</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_contractors" value="1" <?php if(@$access_values[0]['construction_contractors']!=NULL){echo 'checked';}?> /> Contractors</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_add_contractor" value="1" <?php if(@$access_values[0]['construction_add_contractor']!=NULL){echo 'checked';}?> /> Add Contractor</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_contractor_payment" value="1" <?php if(@$access_values[0]['construction_contractor_payment']!=NULL){echo 'checked';}?> /> Contractor Payment</label>
                                        <label class="checkbox-inline"><input type="checkbox" name="construction_reports" value="1" <?php if(@$access_values[0]['construction_reports']!=NULL){echo 'checked';}?> /> Reports</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Inventory Campuses <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <select class="form-control select2 select2_sample1"  name="inventory_campuses[]" multiple>
                                            <?php
                                            foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>" <?php if(in_array($campus['campus_id'], explode(',',@$access_values[0]['inventory_campuses']))){echo 'selected';}?>>
                                                    <?php echo $campus['campus_name'];?>
                                                </option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                        <!--<span class="help-inline"></span>-->
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Product Request Approval Campuses <span class="required">*</span></label>
                                    <div class="col-md-9">
                                        <select class="form-control select2 select2_sample1"  name="product_request_approval_campuses[]" multiple>
                                            <?php
                                            foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>" <?php if(in_array($campus['campus_id'], explode(',',@$access_values[0]['product_request_approval_campuses']))){echo 'selected';}?>>
                                                    <?php echo $campus['campus_name'];?>
                                                </option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                        <!--<span class="help-inline"></span>-->
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label"> Product Purchaser Campuses <span class="required">*</span></label>
                                    <div class="col-md-5">
                                        <select class="form-control select2" id="select2_sample17" name="purchase_campuses[]" multiple>
                                            <?php
                                            foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>" <?php if(in_array($campus['campus_id'], explode(',',@$access_values[0]['purchase_campuses']))){echo 'selected';}?>>
                                                    <?php echo $campus['campus_name'];?>
                                                </option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                        <!--<span class="help-inline"></span>-->
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Schedule Management / Time Table</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="schedule_management_sidebar" value="1" <?php if(@$access_values[0]['schedule_management_sidebar']!=NULL){echo 'checked';}?> /> <strong>Schedule Management Sidebar</strong></label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox2" name="syllabus_sidebar" value="1" <?php if(@$access_values[0]['syllabus_sidebar']!=NULL){echo 'checked';}?> /> <strong>Syllabus Management</strong> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox3" name="make_lecture" value="1" <?php if(@$access_values[0]['make_lecture']!=NULL){echo 'checked';}?> /> Make Lecture of Syllabus </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox4" name="all_lecture" value="1" <?php if(@$access_values[0]['all_lecture']!=NULL){echo 'checked';}?> /> All Lectures </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox5" name="session_wise_syllabus" value="1" <?php if(@$access_values[0]['session_wise_syllabus']!=NULL){echo 'checked';}?> /> Session Wise Syllabus </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox5" name="timetable_sidebar" value="1" <?php if(@$access_values[0]['timetable_sidebar']!=NULL){echo 'checked';}?> /> <strong>Timetable Management</strong> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox5" name="study_type" value="1" <?php if(@$access_values[0]['study_type']!=NULL){echo 'checked';}?> /> Study Type </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox5" name="shifts" value="1" <?php if(@$access_values[0]['shifts']!=NULL){echo 'checked';}?> /> Shifts </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox5" name="add_timetable" value="1" <?php if(@$access_values[0]['add_timetable']!=NULL){echo 'checked';}?> /> Add Timetable </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox5" name="view_timetable" value="1" <?php if(@$access_values[0]['view_timetable']!=NULL){echo 'checked';}?> /> View Timetable </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Archive</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="archive_sidebar" value="1" <?php if(@$access_values[0]['archive_sidebar']!=NULL){echo 'checked';}?> /> Archive Access </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Fee Dues</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="fee_due_sidebar" value="1" <?php if(@$access_values[0]['fee_due_sidebar']!=NULL){echo 'checked';}?> /> Fee Dues Access </label>

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Student Performace</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="student_performance_sidebar" value="1" <?php if(@$access_values[0]['student_performance_sidebar']!=NULL){echo 'checked';}?> /> Student Performance Access </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Holidays</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="holidays_sidebar" value="1" <?php if(@$access_values[0]['holidays_sidebar']!=NULL){echo 'checked';}?> /> Holidays Access </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Supply Students</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="supply_students_sidebar" value="1" <?php if(@$access_values[0]['supply_students_sidebar']!=NULL){echo 'checked';}?> /> Supply Students Access </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Council List</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="council_list_sidebar" value="1" <?php if(@$access_values[0]['council_list_sidebar']!=NULL){echo 'checked';}?> /> Council List Access </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox2" name="create_council_list" value="1" <?php if(@$access_values[0]['create_council_list']!=NULL){echo 'checked';}?> /> Create Council List Access </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox3" name="create_council_list_with_fee" value="1" <?php if(@$access_values[0]['create_council_list_with_fee']!=NULL){echo 'checked';}?> /> Create Council List With Fee Status Access </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Website</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="event_images" value="1" <?php if(@$access_values[0]['event_images']!=NULL){echo 'checked';}?> /> Event Images </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox2" name="slider_images" value="1" <?php if(@$access_values[0]['slider_images']!=NULL){echo 'checked';}?> /> Slider Images </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox3" name="news_updates" value="1" <?php if(@$access_values[0]['news_updates']!=NULL){echo 'checked';}?> /> News &amp; Updates </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox4" name="campuses" value="1" <?php if(@$access_values[0]['campuses']!=NULL){echo 'checked';}?> /> Campuses </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox5" name="sms" value="1" <?php if(@$access_values[0]['sms']!=NULL){echo 'checked';}?> /> SMS </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox6" name="download_documents" value="1" <?php if(@$access_values[0]['download_documents']!=NULL){echo 'checked';}?> /> Download Documents </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Punjab Pharmacy Council</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="punjab_pharmacy_council_access" value="1" <?php if(@$access_values[0]['punjab_pharmacy_council_access']!=NULL){echo 'checked';}?> /> Punjab Pharmacy Council Access </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox2" name="enter_punjab_council_roll_no" value="1" <?php if(@$access_values[0]['enter_punjab_council_roll_no']!=NULL){echo 'checked';}?> /> Enter Punjab Council Roll No </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox3" name="enter_punjab_council_result" value="1" <?php if(@$access_values[0]['enter_punjab_council_result']!=NULL){echo 'checked';}?> /> Enter Punjab Council Result </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox4" name="final_result_pharmacy_technician" value="1" <?php if(@$access_values[0]['final_result_pharmacy_technician']!=NULL){echo 'checked';}?> /> Final Result Pharmacy Technician </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox5" name="add_council_fee" value="1" <?php if(@$access_values[0]['add_council_fee']!=NULL){echo 'checked';}?> /> Add Council Fee </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox6" name="next_exam_status" value="1" <?php if(@$access_values[0]['next_exam_status']!=NULL){echo 'checked';}?> /> Next Exam Status </label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>All Council Report</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="council_report" value="1" <?php if(@$access_values[0]['council_report']!=NULL){echo 'checked';}?> />
                                            Council Report Access
                                        </label>
                                
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox2" name="council_report_add_information_can_add_fee" value="1" <?php if(@$access_values[0]['council_report_add_information_can_add_fee']!=NULL){echo 'checked';}?> />
                                            Add Information
                                        </label>
                                
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox3" name="council_report_add_information_can_add_expense" value="1" <?php if(@$access_values[0]['council_report_add_information_can_add_expense']!=NULL){echo 'checked';}?> />
                                            Add Expense
                                        </label>
                                
                                        <label class="checkbox-inline" style="vertical-align: top;">
                                            Select Campuses <span class="required">*</span>
                                        </label>
                                
                                        <div style="display:inline-block; min-width:300px; vertical-align:middle;" id="inlineCheckbox4">
                                            <select class="form-control select2" id="select2_sample_council_report_colleges" name="council_report_colleges[]" multiple>
                                                
                                                <?php foreach($campuses as $campus): ?>
                                                    <option value="<?php echo $campus['campus_id'];?>" <?php if(in_array($campus['campus_id'], explode(',',@$access_values[0]['council_report_colleges']))){echo 'selected';}?>>
                                                        <?php echo $campus['campus_name'];?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <label class="checkbox-inline" style="vertical-align: top;">Select Councils </label>
                                        <div style="display:inline-block; min-width:300px; vertical-align:middle;" id="inlineCheckbox5">
                                            <select class="form-control select2" id="select2_sample_council_report_courses" name="council_report_courses[]" multiple>
                                                
                                                <?php
                                                $courses = $this->db->get('courses','status = 1')->result_array();
                                                foreach($courses as $assignment_subject):
                                                    ?>
                                                    <option value="<?php echo $assignment_subject['course_id'];?>" <?php if(in_array($assignment_subject['course_id'], explode(',',@$access_values[0]['council_report_courses']))){echo 'selected';}?>>
                                                        <?php echo $assignment_subject['course_name'];?>
                                                    </option>
                                                <?php
                                                endforeach;
                                                ?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Next Council Admisssions</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="next_council_admission_access" value="1" <?php if(@$access_values[0]['next_council_admission_access']!=NULL){echo 'checked';}?> /> Next Council Admisssions Access </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Course Management</strong></label>
                                    <div class="col-md-11 checkbox-list">

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_access" value="1" <?php if(@$access_values[0]['course_management_access']!=NULL){echo 'checked';}?> /> Course Management Access </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_add_course" value="1" <?php if(@$access_values[0]['course_management_add_course']!=NULL){echo 'checked';}?> /> Add Course </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_all_course" value="1" <?php if(@$access_values[0]['course_management_all_course']!=NULL){echo 'checked';}?> /> All Course </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_edit_course" value="1" <?php if(@$access_values[0]['course_management_edit_course']!=NULL){echo 'checked';}?> /> Edit Course </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_delete_course" value="1" <?php if(@$access_values[0]['course_management_delete_course']!=NULL){echo 'checked';}?> /> Delete Course </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_add_subject" value="1" <?php if(@$access_values[0]['course_management_add_subject']!=NULL){echo 'checked';}?> /> Add Suject </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_all_subject" value="1" <?php if(@$access_values[0]['course_management_all_subject']!=NULL){echo 'checked';}?> /> All Suject </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_edit_subject" value="1" <?php if(@$access_values[0]['course_management_edit_subject']!=NULL){echo 'checked';}?> /> Edit Subject </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_delete_subject" value="1" <?php if(@$access_values[0]['course_management_delete_subject']!=NULL){echo 'checked';}?> /> Delete Subject </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_add_chapter" value="1" <?php if(@$access_values[0]['course_management_add_chapter']!=NULL){echo 'checked';}?> /> Add Chapter </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_all_chapter" value="1" <?php if(@$access_values[0]['course_management_all_chapter']!=NULL){echo 'checked';}?> /> All Chapter </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_edit_chapter" value="1" <?php if(@$access_values[0]['course_management_edit_chapter']!=NULL){echo 'checked';}?> /> Edit Chapter </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_delete_chapter" value="1" <?php if(@$access_values[0]['course_management_delete_chapter']!=NULL){echo 'checked';}?> /> Delete Chapter </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_add_topic" value="1" <?php if(@$access_values[0]['course_management_add_topic']!=NULL){echo 'checked';}?> /> Add Topic </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_all_topic" value="1" <?php if(@$access_values[0]['course_management_all_topic']!=NULL){echo 'checked';}?> /> All Topic </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_edit_topic" value="1" <?php if(@$access_values[0]['course_management_edit_topic']!=NULL){echo 'checked';}?> /> Edit Topic </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="course_management_delete_topic" value="1" <?php if(@$access_values[0]['course_management_delete_topic']!=NULL){echo 'checked';}?> /> Delete Topic </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="test_engine_sidebar" value="1" <?php if(@$access_values[0]['test_engine_sidebar']!=NULL){echo 'checked';}?> /> Test Engine Access </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="test_engine_add_practical_books" value="1" <?php if(@$access_values[0]['test_engine_add_practical_books']!=NULL){echo 'checked';}?> /> Add Practical &amp; Books </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="test_engine_add_practical" value="1" <?php if(@$access_values[0]['test_engine_add_practical']!=NULL){echo 'checked';}?> /> Add Practical</label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="test_engine_edit_practical" value="1" <?php if(@$access_values[0]['test_engine_edit_practical']!=NULL){echo 'checked';}?> /> Edit Practical</label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="test_engine_delete_practical" value="1" <?php if(@$access_values[0]['test_engine_delete_practical']!=NULL){echo 'checked';}?> /> Delete Practical</label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="test_engine_books" value="1" <?php if(@$access_values[0]['test_engine_books']!=NULL){echo 'checked';}?> /> Book</label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="test_engine_view_question" value="1" <?php if(@$access_values[0]['test_engine_view_question']!=NULL){echo 'checked';}?> /> Questions </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="test_engine_add_questions" value="1" <?php if(@$access_values[0]['test_engine_add_questions']!=NULL){echo 'checked';}?> /> Add Questions </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="test_engine_edit_question" value="1" <?php if(@$access_values[0]['test_engine_edit_question']!=NULL){echo 'checked';}?> /> Edit Questions </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="test_engine_delete_question" value="1" <?php if(@$access_values[0]['test_engine_delete_question']!=NULL){echo 'checked';}?> /> Delete Questions </label>
                                    </div>
                                </div>

                                <!--<div class="form-group">-->
                                <!--    <label class="col-md-1 control-label"><strong>Test Engine</strong></label>-->
                                <!--    <div class="col-md-11 checkbox-list">-->
                                        
                                <!--    </div>-->
                                <!--</div>-->

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Papers &amp; Results</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="papers_results_sidebar" value="1" <?php if(@$access_values[0]['papers_results_sidebar']!=NULL){echo 'checked';}?> /> Papers &amp; Results Access </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="papers_results_add_paper" value="1" <?php if(@$access_values[0]['papers_results_add_paper']!=NULL){echo 'checked';}?> /> Add Paper </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="papers_results_all_paper" value="1" <?php if(@$access_values[0]['papers_results_all_paper']!=NULL){echo 'checked';}?> /> <strong>All Paper</strong></label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="papers_results_view_paper" value="1" <?php if(@$access_values[0]['papers_results_view_paper']!=NULL){echo 'checked';}?> /> View Paper</label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="papers_results_add_result" value="1" <?php if(@$access_values[0]['papers_results_add_result']!=NULL){echo 'checked';}?> /> Add Result</label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="papers_results_student_results" value="1" <?php if(@$access_values[0]['papers_results_student_results']!=NULL){echo 'checked';}?> /> Student Results</label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="test_system" value="1" <?php if(@$access_values[0]['test_system']!=NULL){echo 'checked';}?> /> Papers &amp; Test and improvement system Management </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="improvement_report" value="1" <?php if(@$access_values[0]['improvement_report']!=NULL){echo 'checked';}?> /> Papers &amp; Test and improvement system Report </label>

                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Assignments</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="assignments_sidebar" value="1" <?php if(@$access_values[0]['assignments_sidebar']!=NULL){echo 'checked';}?> /> Assignments Access </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="assignments_add_assignment" value="1" <?php if(@$access_values[0]['assignments_add_assignment']!=NULL){echo 'checked';}?> /> Add Assignment </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="assignments_all_assignments" value="1" <?php if(@$access_values[0]['assignments_all_assignments']!=NULL){echo 'checked';}?> /> All Assignments</label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="assignments_uncheck_assignments" value="1" <?php if(@$access_values[0]['assignments_uncheck_assignments']!=NULL){echo 'checked';}?> /> Uncheck Assignments</label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="assignments_check_assignments" value="1" <?php if(@$access_values[0]['assignments_check_assignments']!=NULL){echo 'checked';}?> /> Check Assignments</label>


                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label"><strong>Test Engine Subjects</strong></label>
                                    <div class="col-md-5">
                                        <select class="form-control select2" id="select2_my_sample" name="subject_ids[]" multiple>
                                            <?php
                                            foreach($subjects as $subject):
                                                ?>
                                                <option value="<?php echo $subject['course_subject_id'];?>" <?php if(in_array($subject['course_subject_id'], explode(',',@$access_values[0]['test_engine_subject_ids']))){echo 'selected';}?>>
                                                    <?php echo $subject['subject_name'];?>
                                                </option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>HR</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="hr_sidebar" value="1" <?php if(@$access_values[0]['hr_sidebar']!=NULL){echo 'checked';}?> /> HR Access </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox2" name="hr_add_interview" value="1" <?php if(@$access_values[0]['hr_add_interview']!=NULL){echo 'checked';}?> /> Add Interview </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox2" name="hr_edit_interview" value="1" <?php if(@$access_values[0]['hr_edit_interview']!=NULL){echo 'checked';}?> /> Edit Interview </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox2" name="hr_delete_interview" value="1" <?php if(@$access_values[0]['hr_delete_interview']!=NULL){echo 'checked';}?> /> Delete Interview </label>

                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox3" name="hr_all_interview" value="1" <?php if(@$access_values[0]['hr_all_interview']!=NULL){echo 'checked';}?> /> All Interview </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Reminders</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="reminders_sidebar" value="1" <?php if(@$access_values[0]['reminders_sidebar']!=NULL){echo 'checked';}?> /> Reminders Access </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox2" name="reminders_add_rules" value="1" <?php if(@$access_values[0]['reminders_add_rules']!=NULL){echo 'checked';}?> /> Add Reminder Rules </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox3" name="reminders_all_rules" value="1" <?php if(@$access_values[0]['reminders_all_rules']!=NULL){echo 'checked';}?> /> All Reminder Rules </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox3" name="reminders_all_pending" value="1" <?php if(@$access_values[0]['reminders_all_pending']!=NULL){echo 'checked';}?> /> All Pending Reminders </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox3" name="reminders_all_completed" value="1" <?php if(@$access_values[0]['reminders_all_completed']!=NULL){echo 'checked';}?> /> All Completed Reminders </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-1 control-label"><strong>Documents</strong></label>
                                    <div class="col-md-11 checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox1" name="documents_access" value="1" <?php if(@$access_values[0]['documents_access']!=NULL){echo 'checked';}?> /> Documents Access </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox2" name="documents_diploma" value="1" <?php if(@$access_values[0]['documents_diploma']!=NULL){echo 'checked';}?> /> Diploma Documents </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="inlineCheckbox3" name="documents_students" value="1" <?php if(@$access_values[0]['documents_students']!=NULL){echo 'checked';}?> /> Students Documents </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">College Papers & Assignment Subjects </label>
                                    <div class="col-md-5">
                                        <select class="form-control select2" id="select2_sample5" name="assignment_subject_ids[]" multiple>
                                            <?php
                                            foreach($assignment_subjects as $assignment_subject):
                                                ?>
                                                <option value="<?php echo $assignment_subject['course_subject_id'];?>" <?php if(in_array($assignment_subject['course_subject_id'], explode(',',@$access_values[0]['assignment_subject_ids']))){echo 'selected';}?>>
                                                    <?php echo $assignment_subject['subject_name'];?>
                                                </option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                        <!--<span class="help-inline"></span>-->
                                    </div>
                                </div>

                                <div class="form-group" style="display: block">
                                    <label class="col-md-3 control-label">Class <span class="required">*</span></label>
                                    <div class="col-md-5">
                                        <select class="form-control select2" id="select2_sample2" name="class_ids[]" multiple>
                                            <?php
                                            foreach($classes as $class):
                                                ?>
                                                <option value="<?php echo $class['class_id'];?>" selected>
                                                    <?php echo $class['name'];?>
                                                </option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                        <!--<span class="help-inline"></span>-->
                                    </div>
                                </div>



                                <div class="form-group" style="display: none">
                                    <label class="col-md-3 control-label">Fee Dues Campuses <span class="required">*</span></label>
                                    <div class="col-md-5">
                                        <select class="form-control select2" id="select2_sample3" name="fee_dues_campus_ids[]" multiple>
                                            <?php
                                            foreach($campuses as $campus):
                                                ?>
                                                <option value="<?php echo $campus['campus_id'];?>" <?php if(in_array($campus['campus_id'], explode(',',@$access_values[0]['fee_dues_campus_ids']))){echo 'selected';}?>>
                                                    <?php echo $campus['campus_name'];?>
                                                </option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                        <!--<span class="help-inline"></span>-->
                                    </div>
                                </div>
                                <div class="form-group" style="display: none">
                                    <label class="col-md-3 control-label">Classes for fee recovery <span class="required">*</span></label>
                                    <div class="col-md-5">
                                        <select class="form-control select2" id="select2_sample4" name="fee_recovery_class_ids[]" multiple>
                                            <?php
                                            foreach($classes as $class):
                                                ?>
                                                <option value="<?php echo $class['class_id'];?>" <?php if(in_array($class['class_id'], explode(',',@$access_values[0]['fee_recovery_class_ids']))){echo 'selected';}?>>
                                                    <?php echo $class['name'];?>
                                                </option>
                                            <?php
                                            endforeach;
                                            ?>
                                        </select>
                                        <!--<span class="help-inline"></span>-->
                                    </div>
                                </div>
                                <div class="online_appliaction_accesses"  style="display: none">
                                    <?php
                                    foreach($online_applications as $online_application):
                                        ?>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Campus &amp; Cities for online application <span class="required">*</span></label>
                                            <div class="col-md-4">
                                                <select class="form-control" name="application_campus_id[]">
                                                    <?php
                                                    foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>" <?php if($campus['campus_id']==$online_application['campus_id']){echo 'selected';}?>>
                                                            <?php echo $campus['campus_name'];?>
                                                        </option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                                <!--<span class="help-inline"></span>-->
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-control" name="application_city[]">
                                                    <?php
                                                    foreach($cities as $city):
                                                        ?>
                                                        <option value="<?php echo $city;?>" <?php if($city==$online_application['city']){echo 'selected';}?>>
                                                            <?php echo $city;?>
                                                        </option>
                                                    <?php
                                                    endforeach;
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2 checkbox-list">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" id="inlineCheckbox1" name="all_cities[]" value="1" <?php if($online_application['all_cities']==1){echo 'checked';}?> /> All Cities </label>
                                            </div>
                                        </div>
                                    <?php
                                    endforeach;
                                    ?>
                                </div>
                                <div class="col-md-3" style="display: none"></div>
                                <div class="col-md-9"  style="display: none">
                                    <button type="button" class="btn purple add_new_campus"><i class="fa fa-plus"></i> &nbsp;&nbsp; Add Campus &amp; City for Online Application</button>
                                    <button type="button" class="btn red remove_new_campus"><i class="fa fa-trash"></i> &nbsp;&nbsp; Delete</button>
                                </div>
                            </div>

                    </div>
                </div>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <input type="hidden" name="user_id" value="<?php echo @$this->input->post('campus_user_id');?>" />
                            <input type="hidden" name="designation_id" value="<?php echo @$this->input->post('designation_id');?>" />
                            <button type="submit" class="btn green">Add Access</button>
                        </div>
                    </div>
                </div>
                </form>
            </div>
            <?php
            endif;
            ?>
        </div>
        <!-- END EXAMPLE TABLE PORTLET-->
    </div>
</div>
<!-- END PAGE CONTENT-->
</div>
</div>


<script>

    document.addEventListener( "DOMContentLoaded", function(){

        $("#select2_sample6").select2();
        $("#select2_sample10").select2();
        $("#select2_sample17").select2();
        $("#select2_sampletype").select2();
        
        $("#select2_sample_council_report_courses").select2();
        $("#select2_sample_council_report_colleges").select2();

        $('.selection').click(function(){
            var selection = $(this).val();
            $('.access_section').hide();
            $('.'+selection).show();
            if(selection=='by_user')
            {
                jQuery('.department_id').removeAttr('required');
                jQuery('.designations').removeAttr('required');
                jQuery('.user_campus_id').attr('required');
                jQuery('.campus_users').attr('required');
            }
            else if(selection=='by_designation')
            {
                jQuery('.user_campus_id').removeAttr('required');
                jQuery('.campus_users').removeAttr('required');
                jQuery('.department_id').attr('required');
                jQuery('.designations').attr('required');
            }
        });

    }, false );

</script>

<!-- END CONTENT -->
	
	
