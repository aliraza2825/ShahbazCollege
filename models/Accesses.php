<?php
class Accesses extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Employee');
    }

    private function constructionAccessFields()
    {
        return array(
            'construction_sidebar',
            'construction_dashboard',
            'construction_projects',
            'construction_add_project',
            'construction_boq',
            'construction_add_boq',
            'construction_work',
            'construction_issue_material',
            'construction_add_labour',
            'construction_labour_attendance',
            'construction_site_expense',
            'construction_equipment',
            'construction_progress',
            'construction_contractors',
            'construction_add_contractor',
            'construction_contractor_payment',
            'construction_reports'
        );
    }

    private function ensureConstructionAccessColumns()
    {
        foreach (array('access_rules', 'access') as $table) {
            if (!$this->db->table_exists($table)) {
                continue;
            }
            foreach ($this->constructionAccessFields() as $field) {
                if (!$this->db->field_exists($field, $table)) {
                    $this->db->query("ALTER TABLE `$table` ADD `$field` TINYINT(1) NULL DEFAULT NULL");
                }
            }
        }
    }

    private function setConstructionAccessFields()
    {
        $this->ensureConstructionAccessColumns();
        foreach ($this->constructionAccessFields() as $field) {
            $this->db->set($field, $this->input->post($field));
        }
    }

    private function ensureReportAccessColumns()
    {
        foreach (array('access_rules', 'access') as $table) {
            if ($this->db->table_exists($table) && !$this->db->field_exists('reports_discount_report', $table)) {
                $this->db->query("ALTER TABLE `$table` ADD `reports_discount_report` TINYINT(1) NULL DEFAULT NULL");
            }
        }
    }

    private function accountDetailsAccessCheckboxFields()
    {
        return array('account_add_account', 'account_funds_transfer', 'account_edit');
    }

    private function accountDetailsAccessListFields()
    {
        return array(
            'allowed_cash_account_ids',
            'allowed_bank_account_ids',
            'funds_transfer_account_ids',
            'account_details_pettycash_ids'
        );
    }

    private function ensureAccountDetailsAccessColumns()
    {
        foreach (array('access_rules', 'access') as $table) {
            if (!$this->db->table_exists($table)) {
                continue;
            }
            foreach ($this->accountDetailsAccessCheckboxFields() as $field) {
                if (!$this->db->field_exists($field, $table)) {
                    $this->db->query("ALTER TABLE `$table` ADD `$field` TINYINT(1) NULL DEFAULT NULL");
                }
            }
            foreach ($this->accountDetailsAccessListFields() as $field) {
                if (!$this->db->field_exists($field, $table)) {
                    $this->db->query("ALTER TABLE `$table` ADD `$field` TEXT NULL DEFAULT NULL");
                }
            }
        }
    }

    public function getUsers()
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where(array('role!='=>'Admin', 'status'=>1));
        $query = $this->db->get()->result_array();
        return $query;
    }

    public function getClasses()
    {
        $this->db->select('*');
        $this->db->from('classes');
        $query = $this->db->get()->result_array();
        return $query;
    }

    public function getCampuses()
    {
        $this->db->select('*');
        $this->db->from('campuses');
        $query = $this->db->get()->result_array();
        return $query;
    }

    public function getTestEngineSubjects()
    {
        $this->db->select('*');
        $this->db->from('course_subjects');
        $query = $this->db->get()->result_array();
        return $query;
    }

    public function getAssignmentsSubjects()
    {
        $this->db->select('*');
        $this->db->from('course_subjects');
        $query = $this->db->get()->result_array();
        return $query;
    }

    public function check()
    {
        if($this->input->post('user_id'))
        {
            $query = $this->db->get_where('access', array('user_id'=>$this->input->post('user_id')))->result_array();
        }
        elseif($this->input->post('designation_id'))
        {
            $query = $this->db->get_where('access_rules', array('designation_id'=>$this->input->post('designation_id')))->result_array();
        }

        return $query;
    }

    public function addAccess()
    {
        //USER ID
        $user_id = $this->input->post('user_id');
        $designation_id = $this->input->post('designation_id');

        //DASHBOARD
        $dashboard_total_student_box = $this->input->post('dashboard_total_student_box');
        $dashboard_total_teacher_box = $this->input->post('dashboard_total_teacher_box');
        $dashboard_new_admission = $this->input->post('dashboard_new_admission');
        $dashboard_month_earning = $this->input->post('dashboard_month_earning');
        $dashboard_month_expense = $this->input->post('dashboard_month_expense');
        $dashboard_month_profit = $this->input->post('dashboard_month_profit');
        $dashboard_fee_status = $this->input->post('dashboard_fee_status');
        $dashboard_classes_status = $this->input->post('dashboard_classes_status');
        $dashboard_update_payment_box = $this->input->post('dashboard_update_payment_box');
        $dashboard_update_reversal_payment_box = $this->input->post('dashboard_update_reversal_payment_box');
        $dashboard_update_discount_box = $this->input->post('dashboard_update_discount_box');
        $dashboard_update_student_box = $this->input->post('dashboard_update_student_box');
        $dashboard_check_student_box = $this->input->post('dashboard_check_student_box');
        $dashboard_campus_status_box = $this->input->post('dashboard_campus_status_box');
        $dashboard_new_admisssion_entries_box = $this->input->post('dashboard_new_admisssion_entries_box');
        $dashboard_new_expense_entries_box = $this->input->post('dashboard_new_expense_entries_box');
        $dashboard_students_due_fees = $this->input->post('dashboard_students_due_fees');
        $dashboard_students_due_fees_status = $this->input->post('dashboard_students_due_fees_status');
        $dashboard_reminders_status = $this->input->post('dashboard_reminders_status');
        $dashboard_test_engine_questions = $this->input->post('dashboard_test_engine_questions');
        $dashboard_uncheck_assignment = $this->input->post('dashboard_uncheck_assignment');
        $dashboard_students_fees_reversal = $this->input->post('dashboard_students_fees_reversal');
        $payroll_statutory_rules = $this->input->post('payroll_statutory_rules');
        $payroll_income_tax_rules = $this->input->post('payroll_income_tax_rules');

        //ONLINE APPLICATIONS
        $online_application_access = $this->input->post('online_application_access');
        $online_application_new_admissions = $this->input->post('online_application_new_admissions');
        $online_application_checked_admissions = $this->input->post('online_application_checked_admissions');
        $online_application_all = $this->input->post('online_application_all');
        $facebook_leads = $this->input->post('facebook_leads');
        $facebook_leads = $this->input->post('facebook_leads');

        //ACCOUNTS
        $accounts_sidebar = $this->input->post('accounts_sidebar');
        $account_details = $this->input->post('account_details');
        $profit_distribution = $this->input->post('profit_distribution');
        $campus_petty_cash = $this->input->post('campus_petty_cash');
        $advance_system = $this->input->post('advance_system');
        $dailyclosing = $this->input->post('dailyclosing');
        $accounts = $this->input->post('accounts');
        $closing_reconcile = $this->input->post('closing_reconcile');
        $closing_conciliation_edit = $this->input->post('closing_conciliation_edit');
        $bank_reconciliation = $this->input->post('bank_reconciliation');
        $view_campus_closings = $this->input->post('view_campus_closings');
        $campus_closing_ids = $this->input->post('campus_closing_ids');
        $closing_amount_edit = $this->input->post('closing_amount_edit');
        $closing_coo = $this->input->post('closing_coo');
        $misc_income = $this->input->post('misc_income');
        $account_add_account = $this->input->post('account_add_account');
        $account_funds_transfer = $this->input->post('account_funds_transfer');
        $account_edit = $this->input->post('account_edit');
        $allowed_cash_account_ids = $this->input->post('allowed_cash_account_ids');
        $allowed_bank_account_ids = $this->input->post('allowed_bank_account_ids');
        $funds_transfer_account_ids = $this->input->post('funds_transfer_account_ids');
        $account_details_pettycash_ids = $this->input->post('account_details_pettycash_ids');


        //PETTY CASH
        $pettycash_sidebar = $this->input->post('pettycash_sidebar');
        $add_pettycash = $this->input->post('add_pettycash');
        $change_pettycash = $this->input->post('change_pettycash');
        $pettycash_funds_trasfer = $this->input->post('pettycash_funds_trasfer');
        $cash_request = $this->input->post('cash_request');
        $cash_approval = $this->input->post('cash_approval');
        $is_carrier = $this->input->post('is_carrier');

        $petty_cash_users = $this->input->post('petty_cash_users');
        if($petty_cash_users!='')
        {
            $petty_cash_users =  implode(",", $petty_cash_users);
        }


        //ATTENDENCE
        $attendence_sidebar = $this->input->post('attendence_sidebar');
        $attendence_add_attendence = $this->input->post('attendence_add_attendence');
        $attendence_all_attendence = $this->input->post('attendence_all_attendence');
        $attendence_add_types = $this->input->post('attendence_add_types');
        if($attendence_add_types!='')
        {
            $attendence_add_types =  implode(",", $attendence_add_types);
        }

        //Allownces
        $define_allownces = $this->input->post('define_allownces');
        $salary = $this->input->post('salary');
        $loans = $this->input->post('loans');
        $leave_approval = $this->input->post('leave_approval');
        $loan_approval_accounts = $this->input->post('loan_approval_accounts');

        //DEPARTMENT
        $department_sidebar = $this->input->post('department_sidebar');
        $department_add_department = $this->input->post('department_add_department');
        $department_all_department = $this->input->post('department_all_department');
        $department_edit_department = $this->input->post('department_edit_department');
        $department_delete_department = $this->input->post('department_delete_department');

        //DESIGNATION
        $designation_sidebar = $this->input->post('designation_sidebar');
        $designation_add_designation = $this->input->post('designation_add_designation');
        $designation_all_designation = $this->input->post('designation_all_designation');
        $designation_edit_designation = $this->input->post('designation_edit_designation');
        $designation_delete_designation = $this->input->post('designation_delete_designation');

        //STAFF TYPE
        $staff_type_sidebar = $this->input->post('staff_type_sidebar');
        $staff_type_add_staff_type = $this->input->post('staff_type_add_staff_type');
        $staff_type_all_staff_type = $this->input->post('staff_type_all_staff_type');
        $staff_type_edit_staff_type = $this->input->post('staff_type_edit_staff_type');
        $staff_type_delete_staff_type = $this->input->post('staff_type_delete_staff_type');

        //Incentive TYPE
        $recovery_portal = $this->input->post('recovery_portal');
        $all_users_recovery = $this->input->post('all_users_recovery');


        //STAFF STATUS
        $staff_sidebar = $this->input->post('staff_sidebar');
        $staff_add = $this->input->post('staff_add');
        $dailybankclosing = $this->input->post('dailybankclosing');
        $staff_all = $this->input->post('staff_all');
        $staff_edit = $this->input->post('staff_edit');
        $staff_upload_documents = $this->input->post('staff_upload_documents');
        $staff_delete = $this->input->post('staff_delete');
        $staff_attendence = $this->input->post('staff_attendence');
        
        //CLASSES STATUS
        $class_sidebar = $this->input->post('class_sidebar');
        $class_add = $this->input->post('class_add');
        $class_all = $this->input->post('class_all');
        $class_edit = $this->input->post('class_edit');
        $class_delete = $this->input->post('class_delete');
        
        //REPORTS STATUS
        $reports_sidebar = $this->input->post('reports_sidebar');
        $reports_student_fee_problem = $this->input->post('reports_student_fee_problem');
        $all_struckofstudent_report = $this->input->post('all_struckofstudent_report');
        $reports_discount_report = $this->input->post('reports_discount_report');
        $agent_view_statement = $this->input->post('agent_view_statement');
        $agent_view_statement_coo = $this->input->post('agent_view_statement_coo');
        $student_backup_report = $this->input->post('student_backup_report');

        //SUBJECT STATUS
        /*$subject_sidebar = $this->input->post('subject_sidebar');
        $subject_add = $this->input->post('subject_add');
        $subject_all = $this->input->post('subject_all');
        $subject_edit = $this->input->post('subject_edit');
        $subject_delete = $this->input->post('subject_delete');*/
        
        //STUDENTS STATUS
        $student_sidebar = $this->input->post('student_sidebar');
        $student_add = $this->input->post('student_add');
        $student_all = $this->input->post('student_all');
        $student_struck_off_list = $this->input->post('student_struck_off_list');
        $student_edit = $this->input->post('student_edit');
        $student_delete = $this->input->post('student_delete');
        $student_upload_documents = $this->input->post('student_upload_documents');
        $student_payments = $this->input->post('student_payments');
        $student_payment_reset = $this->input->post('student_payment_reset');
        $student_payment_edit = $this->input->post('student_payment_edit');
        $student_payment_delete = $this->input->post('student_payment_delete');
        $student_college_card = $this->input->post('student_college_card');
        $student_issue_refund = $this->input->post('student_issue_refund');
        $can_student_struckof = $this->input->post('can_student_struckof');
        $fine_remove = $this->input->post('fine_remove');
        $discount_reversal = $this->input->post('discount_reversal');
        $installment_date = $this->input->post('installment_date');
        $extra_fee_access = $this->input->post('extra_fee_access');
        $fee_by_cash = $this->input->post('fee_by_cash');
        $fee_by_bank = $this->input->post('fee_by_bank');
        $fee_by_paypro = $this->input->post('fee_by_paypro');
        $change_exam_no_in_payments = $this->input->post('change_exam_no_in_payments');
        $council_list_report = $this->input->post('council_list_report');
        

        //CONTARCTOR STATUS
        $contractor_sidebar = $this->input->post('contractor_sidebar');
        $contract_sidebar = $this->input->post('contract_sidebar');
        $contractor_add = $this->input->post('contractor_add');
        $contractor_all = $this->input->post('contractor_all');
        $contractor_edit = $this->input->post('contractor_edit');
        $contractor_delete = $this->input->post('contractor_delete');
        $contractor_payments = $this->input->post('contractor_payments');
        $contractor_payment_reset = $this->input->post('contractor_payment_reset');
        
        //VISITORS STATUS
        $visitor_sidebar = $this->input->post('visitor_sidebar');
        $visitor_add = $this->input->post('visitor_add');
        $visitor_all = $this->input->post('visitor_all');
        $visitor_edit = $this->input->post('visitor_edit');
        $visitor_delete = $this->input->post('visitor_delete');
        
        //ARCHIVE STATUS
        $archive_sidebar = $this->input->post('archive_sidebar');

        //FEE DUES STATUS
        $fee_due_sidebar = $this->input->post('fee_due_sidebar');
        
        
        //EXPENSE STATUS
        $expense_sidebar = $this->input->post('expense_sidebar');
        $expense_add = $this->input->post('expense_add');
        $expense_add_mobile = $this->input->post('expense_add_mobile');
        $expense_second_approval = $this->input->post('expense_second_approval');
        $expense_all = $this->input->post('expense_all');
        $expense_edit = $this->input->post('expense_edit');
        $expense_delete = $this->input->post('expense_delete');
        $expense_approval = $this->input->post('expense_approval');
        $expense_advertisement_create = $this->input->post('expense_advertisement_create');
        $expense_advertisement_approval = $this->input->post('expense_advertisement_approval');
        $expense_category = $this->input->post('expense_category');
        $expense_view_user = $this->input->post('expense_view_user');
        $expense_no_of_days = $this->input->post('expense_no_of_days');
        $expense_campus_ids = $this->input->post('expense_campus_ids');
        $delete_users_payment = $this->input->post('delete_users_payment');

        if($expense_campus_ids!='')
        {
            $expense_campus_ids =  implode(",", $expense_campus_ids);
        }

        $purchase_campuses = $this->input->post('purchase_campuses');
        $is_purchaser = $this->input->post('is_purchaser');

        if($purchase_campuses!='')
        {
            $purchase_campuses =  implode(",", $purchase_campuses);
        }

        //STUDENT PERFORMANCE STATUS
        $student_performance_sidebar = $this->input->post('student_performance_sidebar');
        
        //HOLIDAYS STATUS
        $holidays_sidebar = $this->input->post('holidays_sidebar');
        
        //SUPPLY STUDENTS STATUS
        $supply_students_sidebar = $this->input->post('supply_students_sidebar');
        
        //COUNCIL LIST STATUS
        $council_list_sidebar = $this->input->post('council_list_sidebar');
        $create_council_list = $this->input->post('create_council_list');
        $create_council_list_with_fee = $this->input->post('create_council_list_with_fee');
        $update_fee_submission = $this->input->post('update_fee_submission');
        
        //WEBSITE MANAGEMENT
        $event_images = $this->input->post('event_images');
        $slider_images = $this->input->post('slider_images');
        $news_updates = $this->input->post('news_updates');
        $campuses = $this->input->post('campuses');
        $sms = $this->input->post('sms');
        $download_documents = $this->input->post('download_documents');
        
        //PUNJAB PHARMACY COUNCIL
        $punjab_pharmacy_council_access = $this->input->post('punjab_pharmacy_council_access');
        $enter_punjab_council_roll_no = $this->input->post('enter_punjab_council_roll_no');
        $enter_punjab_council_result = $this->input->post('enter_punjab_council_result');
        $final_result_pharmacy_technician = $this->input->post('final_result_pharmacy_technician');
        $add_council_fee = $this->input->post('add_council_fee');
        $next_exam_status = $this->input->post('next_exam_status');
        
        //NEXT COUNCIL ADMISSION ACCESS
        $next_council_admission_access = $this->input->post('next_council_admission_access');
        
        //COURSE MANAGEMENT
        $course_management_access = $this->input->post('course_management_access');
        $course_management_add_course = $this->input->post('course_management_add_course');
        $course_management_all_course = $this->input->post('course_management_all_course');
        $course_management_edit_course = $this->input->post('course_management_edit_course');
        $course_management_delete_course = $this->input->post('course_management_delete_course');
        $course_management_add_subject = $this->input->post('course_management_add_subject');
        $course_management_all_subject = $this->input->post('course_management_all_subject');
        $course_management_edit_subject = $this->input->post('course_management_edit_subject');
        $course_management_delete_subject = $this->input->post('course_management_delete_subject');
        $course_management_add_chapter = $this->input->post('course_management_add_chapter');
        $course_management_all_chapter = $this->input->post('course_management_all_chapter');
        $course_management_edit_chapter = $this->input->post('course_management_edit_chapter');
        $course_management_delete_chapter = $this->input->post('course_management_delete_chapter');
        $course_management_add_topic = $this->input->post('course_management_add_topic');
        $course_management_all_topic = $this->input->post('course_management_all_topic');
        $course_management_edit_topic = $this->input->post('course_management_edit_topic');
        $course_management_delete_topic = $this->input->post('course_management_delete_topic');
        
        //TEST ENGINE
        $test_engine_sidebar = $this->input->post('test_engine_sidebar');
        $test_engine_add_practical_books = $this->input->post('test_engine_add_practical_books');
        $test_engine_add_practical = $this->input->post('test_engine_add_practical');
        $test_engine_edit_practical = $this->input->post('test_engine_edit_practical');
        $test_engine_delete_practical = $this->input->post('test_engine_delete_practical');
        $test_engine_books = $this->input->post('test_engine_books');
        $test_engine_view_question = $this->input->post('test_engine_view_question');
        $test_engine_add_questions = $this->input->post('test_engine_add_questions');
        $test_engine_edit_question = $this->input->post('test_engine_edit_question');
        $test_engine_delete_question = $this->input->post('test_engine_delete_question');
        $subject_ids = $this->input->post('subject_ids');
        if($subject_ids!='')
        {
            $subject_ids =  implode(",", $subject_ids);
        }
        $test_engine_books = $this->input->post('test_engine_books');

        //PAPERS & RESULTS
        $papers_results_sidebar = $this->input->post('papers_results_sidebar');
        $papers_results_add_paper = $this->input->post('papers_results_add_paper');
        $papers_results_all_paper = $this->input->post('papers_results_all_paper');
        $papers_results_view_paper = $this->input->post('papers_results_view_paper');
        $papers_results_add_result = $this->input->post('papers_results_add_result');
        $papers_results_student_results = $this->input->post('papers_results_student_results');
        $test_system = $this->input->post('test_system');
        $improvement_report = $this->input->post('improvement_report');

        //SCHEDULE MANAGEMENT TIME TABLE
        $schedule_management_sidebar = $this->input->post('schedule_management_sidebar');
        $syllabus_sidebar = $this->input->post('syllabus_sidebar');
        $make_lecture = $this->input->post('make_lecture');
        $all_lecture = $this->input->post('all_lecture');
        $session_wise_syllabus = $this->input->post('session_wise_syllabus');
        $timetable_sidebar = $this->input->post('timetable_sidebar');
        $study_type = $this->input->post('study_type');
        $shifts = $this->input->post('shifts');
        $add_timetable = $this->input->post('add_timetable');
        $view_timetable = $this->input->post('view_timetable');

        //ASSIGNMENTS
        $assignments_sidebar = $this->input->post('assignments_sidebar');
        $assignments_add_assignment = $this->input->post('assignments_add_assignment');
        $assignments_all_assignments = $this->input->post('assignments_all_assignments');
        $assignments_uncheck_assignments = $this->input->post('assignments_uncheck_assignments');
        $assignments_check_assignments = $this->input->post('assignments_check_assignments');

        //ASSIGNMENT
        $assignment_subject_ids = $this->input->post('assignment_subject_ids');
        if($assignment_subject_ids!='')
        {
            $assignment_subject_ids =  implode(",", $assignment_subject_ids);
        }

        //HR
        $hr_sidebar = $this->input->post('hr_sidebar');
        $hr_add_interview = $this->input->post('hr_add_interview');
        $hr_edit_interview = $this->input->post('hr_edit_interview');
        $hr_delete_interview = $this->input->post('hr_delete_interview');
        $hr_all_interview = $this->input->post('hr_all_interview');
        
        //REMINDERS
        $reminders_sidebar = $this->input->post('reminders_sidebar');
        $reminders_add_rules = $this->input->post('reminders_add_rules');
        $reminders_all_rules = $this->input->post('reminders_all_rules');
        $reminders_all_pending = $this->input->post('reminders_all_pending');
        $reminders_all_completed = $this->input->post('reminders_all_completed');
        
        //DOCUMENTS
        $documents_access = $this->input->post('documents_access');
        $documents_diploma = $this->input->post('documents_diploma');
        $documents_students = $this->input->post('documents_students');
        
        //CLASS MANAGEMENT
        $class_ids = $this->input->post('class_ids');
        if($class_ids!='')
        {
            $class_ids =  implode(",", $class_ids);
        }
        
        //CAMPUS MANAGEMENT
        $campus_ids = $this->input->post('campus_ids');
        if($campus_ids!='')
        {
            $campus_ids =  implode(",", $campus_ids);
        }
        
        //FEE DUES CAMPUS MANAGEMENT
        $fee_dues_campus_ids = $this->input->post('fee_dues_campus_ids');
        if($fee_dues_campus_ids!='')
        {
            $fee_dues_campus_ids =  implode(",", $fee_dues_campus_ids);
        }
        
        //FEE RECOVERY CLASSES ACCESS
        $fee_recovery_class_ids = $this->input->post('fee_recovery_class_ids');
        if($fee_recovery_class_ids!='')
        {
            $fee_recovery_class_ids = implode(",", $fee_recovery_class_ids);
        }
        
        //CITIES
        $cities = $this->input->post('cities');
        if($cities!='')
        {
            $cities =  implode(",", $cities);
        }
        if($campus_closing_ids!='')
        {
            $campus_closing_ids =  implode(",", $campus_closing_ids);
        }
        if($allowed_cash_account_ids!='')
        {
            $allowed_cash_account_ids = implode(",", $allowed_cash_account_ids);
        }
        if($allowed_bank_account_ids!='')
        {
            $allowed_bank_account_ids = implode(",", $allowed_bank_account_ids);
        }
        if($funds_transfer_account_ids!='')
        {
            $funds_transfer_account_ids = implode(",", $funds_transfer_account_ids);
        }
        if($account_details_pettycash_ids!='')
        {
            $account_details_pettycash_ids = implode(",", $account_details_pettycash_ids);
        }
        $other_cities_access = $this->input->post('other_cities_access');

        $attendance_mobile_report = $this->input->post('attendance_mobile_report');
        $session_students_mobile_report = $this->input->post('session_students_mobile_report');
        $how_to_use = $this->input->post('how_to_use');

        //INVENTORY
        $inventory = $this->input->post('inventory');
        $add_vendor = $this->input->post('add_vendor');
        $manage_vendor = $this->input->post('manage_vendor');
        $edit_vendor = $this->input->post('edit_vendor');
        $delete_vendor = $this->input->post('delete_vendor');
        $add_purchase_request = $this->input->post('add_purchase_request');
        $all_purchase_request = $this->input->post('all_purchase_request');
        $edit_purchase_request = $this->input->post('edit_purchase_request');
        $delete_purchase_request = $this->input->post('delete_purchase_request');
        $add_qoutation = $this->input->post('add_qoutation');
        $approve_qoutation = $this->input->post('approve_qoutation');
        $purchase_orders = $this->input->post('purchase_orders');
        $grn_gate_approval = $this->input->post('grn_gate_approval');
        $grn_approval = $this->input->post('grn_approval');
        $add_room = $this->input->post('add_room');
        $all_room = $this->input->post('all_room');
        $edit_room = $this->input->post('edit_room');
        $delete_room = $this->input->post('delete_room');
        $add_subroom = $this->input->post('add_subroom');
        $all_subroom = $this->input->post('all_subroom');
        $edit_subroom = $this->input->post('edit_subroom');
        $delete_subroom = $this->input->post('delete_subroom');
        $manage_product_names = $this->input->post('manage_product_names');
        $manage_document_names = $this->input->post('manage_document_names');
        $add_product = $this->input->post('add_product');
        $all_product = $this->input->post('all_product');
        $add_product_issue_request = $this->input->post('add_product_issue_request');
        $all_product_issue_request = $this->input->post('all_product_issue_request');
        $manage_gin = $this->input->post('manage_gin');
        $manage_grn = $this->input->post('manage_grn');
        $generate_qrs = $this->input->post('generate_qrs');
        $product_return_request = $this->input->post('product_return_request');
        $approve_product_return_request = $this->input->post('approve_product_return_request');
        $inventory_campuses = $this->input->post('inventory_campuses');
        if($inventory_campuses!='')
        {
            $inventory_campuses =  implode(",", $inventory_campuses);
        }
        $product_request_approval_campuses = $this->input->post('product_request_approval_campuses');
        if($product_request_approval_campuses!='')
        {
            $product_request_approval_campuses =  implode(",", $product_request_approval_campuses);
        }
        
        $council_report = $this->input->post('council_report');
        $council_report_add_information_can_add_fee = $this->input->post('council_report_add_information_can_add_fee');
        $council_report_add_information_can_add_expense = $this->input->post('council_report_add_information_can_add_expense');
        
        $council_report_colleges = $this->input->post('council_report_colleges');
        if($council_report_colleges!='')
        {
            $council_report_colleges =  implode(",", $council_report_colleges);
        }
        
        $council_report_courses = $this->input->post('council_report_courses');
        if($council_report_courses!='')
        {
            $council_report_courses =  implode(",", $council_report_courses);
        }

        $apply_loan = $this->input->post('apply_loan');
        $this->db->set('apply_loan', $apply_loan);
        
        $this->db->set('payroll_statutory_rules', $payroll_statutory_rules);
        $this->db->set('payroll_income_tax_rules', $payroll_income_tax_rules);
        
        
        $this->db->set('council_report_colleges', $council_report_colleges);
        $this->db->set('council_report_courses', $council_report_courses);

        $this->db->set('dashboard_total_student_box', $dashboard_total_student_box);
        $this->db->set('dashboard_total_teacher_box', $dashboard_total_teacher_box);
        $this->db->set('dashboard_new_admission', $dashboard_new_admission);
        $this->db->set('dashboard_month_earning', $dashboard_month_earning);
        $this->db->set('dashboard_month_expense', $dashboard_month_expense);
        $this->db->set('dashboard_month_profit', $dashboard_month_profit);
        $this->db->set('dashboard_fee_status', $dashboard_fee_status);
        $this->db->set('dashboard_classes_status', $dashboard_classes_status);
        $this->db->set('dashboard_update_payment_box', $dashboard_update_payment_box);
        $this->db->set('dashboard_update_discount_box', $dashboard_update_discount_box);
        $this->db->set('dashboard_update_student_box', $dashboard_update_student_box);
        $this->db->set('dashboard_check_student_box', $dashboard_check_student_box);
        $this->db->set('dashboard_campus_status_box', $dashboard_campus_status_box);
        $this->db->set('dashboard_new_admisssion_entries_box', $dashboard_new_admisssion_entries_box);
        $this->db->set('dashboard_new_expense_entries_box', $dashboard_new_expense_entries_box);
        $this->db->set('dashboard_students_due_fees', $dashboard_students_due_fees);
        $this->db->set('dashboard_students_due_fees_status', $dashboard_students_due_fees_status);
        $this->db->set('dashboard_reminders_status', $dashboard_reminders_status);
        $this->db->set('dashboard_test_engine_questions', $dashboard_test_engine_questions);
        $this->db->set('dashboard_uncheck_assignment', $dashboard_uncheck_assignment);
        $this->db->set('dashboard_update_reversal_payment_box', $dashboard_update_reversal_payment_box);
        $this->db->set('dashboard_students_fees_reversal', $dashboard_students_fees_reversal);
        $this->db->set('how_to_use', $how_to_use);

        $this->db->set('online_application_access', $online_application_access);
        $this->db->set('online_application_new_admissions', $online_application_new_admissions);
        $this->db->set('online_application_checked_admissions', $online_application_checked_admissions);
        $this->db->set('online_application_all', $online_application_all);
        $this->db->set('facebook_leads', $facebook_leads);

        $this->db->set('accounts_sidebar', $accounts_sidebar);
        $this->db->set('account_details', $account_details);
        $this->db->set('profit_distribution', $profit_distribution);
        $this->db->set('campus_petty_cash', $campus_petty_cash);
        $this->db->set('advance_system', $advance_system);
        $this->db->set('dailyclosing', $dailyclosing);
        $this->db->set('dailybankclosing', $dailybankclosing);
        $this->db->set('accounts', $accounts);
        $this->db->set('closing_reconcile', $closing_reconcile);
        $this->db->set('closing_conciliation_edit', $closing_conciliation_edit);
        $this->db->set('bank_reconciliation', $bank_reconciliation);
        $this->db->set('view_campus_closings', $view_campus_closings);
        $this->db->set('campus_closing_ids', $campus_closing_ids);
        $this->db->set('closing_amount_edit', $closing_amount_edit);
        $this->db->set('closing_coo', $closing_coo);
        $this->db->set('misc_income', $misc_income);
        $this->ensureAccountDetailsAccessColumns();
        $this->db->set('account_add_account', $account_add_account);
        $this->db->set('account_funds_transfer', $account_funds_transfer);
        $this->db->set('account_edit', $account_edit);
        $this->db->set('allowed_cash_account_ids', $allowed_cash_account_ids);
        $this->db->set('allowed_bank_account_ids', $allowed_bank_account_ids);
        $this->db->set('funds_transfer_account_ids', $funds_transfer_account_ids);
        $this->db->set('account_details_pettycash_ids', $account_details_pettycash_ids);

        $this->db->set('pettycash_sidebar', $pettycash_sidebar);
        $this->db->set('add_pettycash', $add_pettycash);
        $this->db->set('change_pettycash', $change_pettycash);
        $this->db->set('pettycash_funds_trasfer', $pettycash_funds_trasfer);
        $this->db->set('petty_cash_users', $petty_cash_users);
        $this->db->set('cash_request', $cash_request);
        $this->db->set('cash_approval', $cash_approval);
        $this->db->set('is_carrier', $is_carrier);

        $this->db->set('attendence_sidebar', $attendence_sidebar);
        $this->db->set('attendence_add_attendence', $attendence_add_attendence);
        $this->db->set('attendence_all_attendence', $attendence_all_attendence);
        $this->db->set('attendence_add_types', $attendence_add_types);

        $this->db->set('department_sidebar', $department_sidebar);
        $this->db->set('department_add_department', $department_add_department);
        $this->db->set('department_all_department', $department_all_department);
        $this->db->set('department_edit_department', $department_edit_department);
        $this->db->set('department_delete_department', $department_delete_department);

        $this->db->set('designation_sidebar', $designation_sidebar);
        $this->db->set('designation_add_designation', $designation_add_designation);
        $this->db->set('designation_all_designation', $designation_all_designation);
        $this->db->set('designation_edit_designation', $designation_edit_designation);
        $this->db->set('designation_delete_designation', $designation_delete_designation);

        $this->db->set('staff_type_sidebar', $staff_type_sidebar);
        $this->db->set('staff_type_add_staff_type', $staff_type_add_staff_type);
        $this->db->set('staff_type_all_staff_type', $staff_type_all_staff_type);
        $this->db->set('staff_type_edit_staff_type', $staff_type_edit_staff_type);
        $this->db->set('staff_type_delete_staff_type', $staff_type_delete_staff_type);

        $this->db->set('recovery_portal', $recovery_portal);
        $this->db->set('all_users_recovery', $all_users_recovery);
        $this->db->set('salary', $salary);
        $this->db->set('loan_approval', $loans);
        $this->db->set('leave_approval', $leave_approval);
        $this->db->set('loan_approval_accounts', $loan_approval_accounts);

        $this->db->set('staff_sidebar', $staff_sidebar);
        $this->db->set('staff_add', $staff_add);
        $this->db->set('staff_all', $staff_all);
        $this->db->set('staff_edit', $staff_edit);
        $this->db->set('staff_upload_documents', $staff_upload_documents);
        $this->db->set('staff_delete', $staff_delete);
        $this->db->set('staff_attendence', $staff_attendence);

        $this->db->set('class_sidebar', $class_sidebar);
        $this->db->set('class_add', $class_add);
        $this->db->set('class_all', $class_all);
        $this->db->set('class_edit', $class_edit);
        $this->db->set('class_delete', $class_delete);

        $this->db->set('reports_sidebar', $reports_sidebar);
        $this->db->set('reports_student_fee_problem', $reports_student_fee_problem);
        $this->db->set('all_struckofstudent_report', $all_struckofstudent_report);
        $this->ensureReportAccessColumns();
        $this->db->set('reports_discount_report', $reports_discount_report);
        /*$this->db->set('subject_sidebar', $subject_sidebar);
        $this->db->set('subject_add', $subject_add);
        $this->db->set('subject_all', $subject_all);
        $this->db->set('subject_edit', $subject_edit);
        $this->db->set('subject_delete', $subject_delete);*/

        $this->db->set('student_sidebar', $student_sidebar);
        $this->db->set('student_add', $student_add);
        $this->db->set('student_all', $student_all);
        $this->db->set('student_struck_off_list', $student_struck_off_list);
        $this->db->set('student_edit', $student_edit);
        $this->db->set('student_delete', $student_delete);
        $this->db->set('student_upload_documents', $student_upload_documents);
        $this->db->set('student_payments', $student_payments);
        $this->db->set('student_payment_reset', $student_payment_reset);
        $this->db->set('student_payment_edit', $student_payment_edit);
        $this->db->set('student_payment_delete', $student_payment_delete);
        $this->db->set('student_college_card', $student_college_card);
        $this->db->set('student_issue_refund', $student_issue_refund);
        $this->db->set('update_fee_submission', $update_fee_submission);
        $this->db->set('delete_users_payment', $delete_users_payment);
        $this->db->set('fine_remove', $fine_remove);
        $this->db->set('discount_reversal', $discount_reversal);
        $this->db->set('installment_date', $installment_date);
        $this->db->set('extra_fee_access', $extra_fee_access);
        $this->db->set('fee_by_cash', $fee_by_cash);
        $this->db->set('fee_by_bank', $fee_by_bank);
        $this->db->set('fee_by_paypro', $fee_by_paypro);
        $this->db->set('change_exam_no_in_payments', $change_exam_no_in_payments);
        $this->db->set('council_list_report', $council_list_report);
        
        $receipt_book = $this->input->post('receipt_book');
        $computer_challan = $this->input->post('computer_challan');
        $archived_students = $this->input->post('archived_students');
        $change_exam_no_in_payments = $this->input->post('change_exam_no_in_payments');

        $this->db->set('receipt_book', $receipt_book);
        $this->db->set('computer_challan', $computer_challan);
        $this->db->set('archived_students', $archived_students);


        $this->db->set('contractor_sidebar', $contractor_sidebar);
        $this->db->set('contract_sidebar', $contract_sidebar);
        $this->db->set('contractor_add', $contractor_add);
        $this->db->set('contractor_all', $contractor_all);
        $this->db->set('contractor_edit', $contractor_edit);
        $this->db->set('contractor_delete', $contractor_delete);
        $this->db->set('contractor_payments', $contractor_payments);
        $this->db->set('contractor_payment_reset', $contractor_payment_reset);

        $this->db->set('visitor_sidebar', $visitor_sidebar);
        $this->db->set('visitor_add', $visitor_add);
        $this->db->set('visitor_all', $visitor_all);
        $this->db->set('visitor_edit', $visitor_edit);
        $this->db->set('visitor_delete', $visitor_delete);

        $this->db->set('archive_sidebar', $archive_sidebar);

        $this->db->set('attendence_sidebar', $attendence_sidebar);

        $this->db->set('fee_due_sidebar', $fee_due_sidebar);

        $this->db->set('expense_sidebar', $expense_sidebar);
        $this->db->set('expense_add', $expense_add);
        $this->db->set('expense_add_mobile', $expense_add_mobile);
        $this->db->set('expense_second_approval', $expense_second_approval);
        $this->db->set('expense_all', $expense_all);
        $this->db->set('expense_edit', $expense_edit);
        $this->db->set('expense_delete', $expense_delete);
        $this->db->set('expense_approval', $expense_approval);
        $this->db->set('expense_category', $expense_category);
        $this->db->set('expense_campus_ids', $expense_campus_ids);
        $this->db->set('expense_view_user', $expense_view_user);
        $this->db->set('expense_no_of_days', $expense_no_of_days);
        $this->db->set('expense_advertisement_approval', $expense_advertisement_approval);
        $this->db->set('expense_advertisement_create', $expense_advertisement_create);


        $this->db->set('student_performance_sidebar', $student_performance_sidebar);

        $this->db->set('supply_students_sidebar', $supply_students_sidebar);

        $this->db->set('holidays_sidebar', $holidays_sidebar);

        $this->db->set('council_list_sidebar', $council_list_sidebar);
        $this->db->set('create_council_list', $create_council_list);
        $this->db->set('create_council_list_with_fee', $create_council_list_with_fee);

        $this->db->set('event_images', $event_images);
        $this->db->set('slider_images', $slider_images);
        $this->db->set('news_updates', $news_updates);
        $this->db->set('campuses', $campuses);
        $this->db->set('sms', $sms);
        $this->db->set('download_documents', $download_documents);

        $this->db->set('punjab_pharmacy_council_access', $punjab_pharmacy_council_access);
        $this->db->set('enter_punjab_council_roll_no', $enter_punjab_council_roll_no);
        $this->db->set('enter_punjab_council_result', $enter_punjab_council_result);
        $this->db->set('final_result_pharmacy_technician', $final_result_pharmacy_technician);
        $this->db->set('add_council_fee', $add_council_fee);
        $this->db->set('next_exam_status', $next_exam_status);

        $this->db->set('next_council_admission_access', $next_council_admission_access);
        $this->db->set('define_allownces', $define_allownces);

        $this->db->set('course_management_access', $course_management_access);
        $this->db->set('course_management_add_course', $course_management_add_course);
        $this->db->set('course_management_all_course', $course_management_all_course);
        $this->db->set('course_management_edit_course', $course_management_edit_course);
        $this->db->set('course_management_delete_course', $course_management_delete_course);
        $this->db->set('course_management_add_subject', $course_management_add_subject);
        $this->db->set('course_management_all_subject', $course_management_all_subject);
        $this->db->set('course_management_edit_subject', $course_management_edit_subject);
        $this->db->set('course_management_delete_subject', $course_management_delete_subject);
        $this->db->set('course_management_add_chapter', $course_management_add_chapter);
        $this->db->set('course_management_all_chapter', $course_management_all_chapter);
        $this->db->set('course_management_edit_chapter', $course_management_edit_chapter);
        $this->db->set('course_management_delete_chapter', $course_management_delete_chapter);
        $this->db->set('course_management_add_topic', $course_management_add_topic);
        $this->db->set('course_management_all_topic', $course_management_all_topic);
        $this->db->set('course_management_edit_topic', $course_management_edit_topic);
        $this->db->set('course_management_delete_topic', $course_management_delete_topic);

        $this->db->set('test_engine_sidebar', $test_engine_sidebar);
        $this->db->set('test_engine_add_practical_books', $test_engine_add_practical_books);
        $this->db->set('test_engine_add_practical', $test_engine_add_practical);
        $this->db->set('test_engine_edit_practical', $test_engine_edit_practical);
        $this->db->set('test_engine_delete_practical', $test_engine_delete_practical);
        $this->db->set('test_engine_books', $test_engine_books);
        $this->db->set('test_engine_view_question', $test_engine_view_question);
        $this->db->set('test_engine_add_questions', $test_engine_add_questions);
        $this->db->set('test_engine_edit_question', $test_engine_edit_question);
        $this->db->set('test_engine_delete_question', $test_engine_delete_question);
        $this->db->set('test_engine_subject_ids', $subject_ids);
        $this->db->set('test_engine_books', $test_engine_books);

        $this->db->set('papers_results_sidebar', $papers_results_sidebar);
        $this->db->set('papers_results_add_paper', $papers_results_add_paper);
        $this->db->set('papers_results_all_paper', $papers_results_all_paper);
        $this->db->set('papers_results_view_paper', $papers_results_view_paper);
        $this->db->set('papers_results_add_result', $papers_results_add_result);
        $this->db->set('papers_results_student_results', $papers_results_student_results);
        $this->db->set('test_system', $test_system);
        $this->db->set('improvement_report', $improvement_report);


        $this->db->set('schedule_management_sidebar', $schedule_management_sidebar);
        $this->db->set('syllabus_sidebar', $syllabus_sidebar);
        $this->db->set('make_lecture', $make_lecture);
        $this->db->set('all_lecture', $all_lecture);
        $this->db->set('session_wise_syllabus', $session_wise_syllabus);
        $this->db->set('timetable_sidebar', $timetable_sidebar);
        $this->db->set('study_type', $study_type);
        $this->db->set('shifts', $shifts);
        $this->db->set('add_timetable', $add_timetable);
        $this->db->set('view_timetable', $view_timetable);

        $this->db->set('assignments_sidebar', $assignments_sidebar);
        $this->db->set('assignments_add_assignment', $assignments_add_assignment);
        $this->db->set('assignments_all_assignments', $assignments_all_assignments);
        $this->db->set('assignments_uncheck_assignments', $assignments_uncheck_assignments);
        $this->db->set('assignments_check_assignments', $assignments_check_assignments);


        $this->db->set('assignment_subject_ids', $assignment_subject_ids);

        $this->db->set('hr_sidebar', $hr_sidebar);
        $this->db->set('hr_add_interview', $hr_add_interview);
        $this->db->set('hr_edit_interview', $hr_edit_interview);
        $this->db->set('hr_delete_interview', $hr_delete_interview);
        $this->db->set('hr_all_interview', $hr_all_interview);

        $this->db->set('reminders_sidebar', $reminders_sidebar);
        $this->db->set('reminders_add_rules', $reminders_add_rules);
        $this->db->set('reminders_all_rules', $reminders_all_rules);
        $this->db->set('reminders_all_pending', $reminders_all_pending);
        $this->db->set('reminders_all_completed', $reminders_all_completed);

        $this->db->set('documents_access', $documents_access);
        $this->db->set('documents_diploma', $documents_diploma);
        $this->db->set('documents_students', $documents_students);

        $this->db->set('class_ids', $class_ids);

        $this->db->set('campus_ids', $campus_ids);
        $this->db->set('attendance_mobile_report', $attendance_mobile_report);
        $this->db->set('session_students_mobile_report', $session_students_mobile_report);

        $this->db->set('fee_dues_campus_ids', $fee_dues_campus_ids);

        $this->db->set('fee_recovery_class_ids', $fee_recovery_class_ids);
        $this->db->set('cities', $cities);
        $this->db->set('other_cities_access', $other_cities_access);
        $this->db->set('is_purchaser', $is_purchaser);
        $this->db->set('purchase_campuses', $purchase_campuses);
        $this->db->set('agent_view_statement', $agent_view_statement);
        $this->db->set('agent_view_statement_coo', $agent_view_statement_coo);
        $this->db->set('student_backup_report', $student_backup_report);

        $this->db->set('inventory',$inventory);
        $this->db->set('add_vendor',$add_vendor);
        $this->db->set('manage_vendor',$manage_vendor);
        $this->db->set('edit_vendor',$edit_vendor);
        $this->db->set('delete_vendor',$delete_vendor);
        $this->db->set('add_purchase_request',$add_purchase_request);
        $this->db->set('all_purchase_request',$all_purchase_request);
        $this->db->set('edit_purchase_request',$edit_purchase_request);
        $this->db->set('delete_purchase_request',$delete_purchase_request);
        $this->db->set('add_qoutation',$add_qoutation);
        $this->db->set('approve_qoutation',$approve_qoutation);
        $this->db->set('purchase_orders',$purchase_orders);
        $this->db->set('grn_gate_approval',$grn_gate_approval);
        $this->db->set('grn_approval',$grn_approval);
        $this->db->set('add_room',$add_room);
        $this->db->set('all_room',$all_room);
        $this->db->set('edit_room',$edit_room);
        $this->db->set('delete_room',$delete_room);
        $this->db->set('add_subroom',$add_subroom);
        $this->db->set('all_subroom',$all_subroom);
        $this->db->set('edit_subroom',$edit_subroom);
        $this->db->set('delete_subroom',$delete_subroom);
        $this->db->set('manage_product_names',$manage_product_names);
        $this->db->set('manage_document_names',$manage_document_names);
        $this->db->set('add_product',$add_product);
        $this->db->set('all_product',$all_product);
        $this->db->set('add_product_issue_request',$add_product_issue_request);
        $this->db->set('all_product_issue_request',$all_product_issue_request);
        $this->db->set('manage_gin',$manage_gin);
        $this->db->set('manage_grn',$manage_grn);
        $this->db->set('generate_qrs',$generate_qrs);
        $this->db->set('product_return_request',$product_return_request);
        $this->db->set('approve_product_return_request',$approve_product_return_request);
        $this->db->set('inventory_campuses',$inventory_campuses);
        $this->db->set('product_request_approval_campuses',$product_request_approval_campuses);
        
        $this->db->set('council_report',$council_report);
        $this->db->set('council_report_add_information_can_add_fee',$council_report_add_information_can_add_fee);
        $this->db->set('council_report_add_information_can_add_expense',$council_report_add_information_can_add_expense);
        $this->setConstructionAccessFields();
        

        if($user_id!='')
        {
            $this->db->set('user_id', $user_id);
            $this->db->insert('access');
        }
        else
        {
            $this->db->set('designation_id', $designation_id);
            $this->db->insert('access_rules');
            $this->updateUsersAccess($designation_id);
        }
    }

    public function updateAccess()
    {
        //USER ID
        $user_id = $this->input->post('user_id');
        $designation_id = $this->input->post('designation_id');
        //DASHBOARD
        $dashboard_total_student_box = $this->input->post('dashboard_total_student_box');
        $dashboard_total_teacher_box = $this->input->post('dashboard_total_teacher_box');
        $dashboard_new_admission = $this->input->post('dashboard_new_admission');
        $dashboard_month_earning = $this->input->post('dashboard_month_earning');
        $dashboard_month_expense = $this->input->post('dashboard_month_expense');
        $dashboard_month_profit = $this->input->post('dashboard_month_profit');
        $dashboard_fee_status = $this->input->post('dashboard_fee_status');
        $dashboard_classes_status = $this->input->post('dashboard_classes_status');
        $dashboard_update_payment_box = $this->input->post('dashboard_update_payment_box');
        $dashboard_update_discount_box = $this->input->post('dashboard_update_discount_box');
        $dashboard_update_student_box = $this->input->post('dashboard_update_student_box');
        $dashboard_check_student_box = $this->input->post('dashboard_check_student_box');
        $dashboard_campus_status_box = $this->input->post('dashboard_campus_status_box');
        $dashboard_new_admisssion_entries_box = $this->input->post('dashboard_new_admisssion_entries_box');
        $dashboard_new_expense_entries_box = $this->input->post('dashboard_new_expense_entries_box');
        $dashboard_students_due_fees = $this->input->post('dashboard_students_due_fees');
        $dashboard_students_due_fees_status = $this->input->post('dashboard_students_due_fees_status');
        $dashboard_reminders_status = $this->input->post('dashboard_reminders_status');
        $dashboard_test_engine_questions = $this->input->post('dashboard_test_engine_questions');
        $dashboard_uncheck_assignment = $this->input->post('dashboard_uncheck_assignment');
        $dashboard_update_reversal_payment_box = $this->input->post('dashboard_update_reversal_payment_box');
        $dashboard_students_fees_reversal = $this->input->post('dashboard_students_fees_reversal');

        //ONLINE APPLICATIONS
        $online_application_access = $this->input->post('online_application_access');
        $online_application_new_admissions = $this->input->post('online_application_new_admissions');
        $online_application_checked_admissions = $this->input->post('online_application_checked_admissions');
        $online_application_all = $this->input->post('online_application_all');
        $facebook_leads = $this->input->post('facebook_leads');

        //ACCOUNTS
        $accounts_sidebar = $this->input->post('accounts_sidebar');
        $account_details = $this->input->post('account_details');
        $profit_distribution = $this->input->post('profit_distribution');
        $campus_petty_cash = $this->input->post('campus_petty_cash');
        $advance_system = $this->input->post('advance_system');
        $dailyclosing = $this->input->post('dailyclosing');
        $accounts = $this->input->post('accounts');
        $closing_reconcile = $this->input->post('closing_reconcile');
        $closing_conciliation_edit = $this->input->post('closing_conciliation_edit');
        $bank_reconciliation = $this->input->post('bank_reconciliation');
        $view_campus_closings = $this->input->post('view_campus_closings');
        $campus_closing_ids = $this->input->post('campus_closing_ids');
        $closing_amount_edit = $this->input->post('closing_amount_edit');
        $closing_coo = $this->input->post('closing_coo');
        $misc_income = $this->input->post('misc_income');
        $account_add_account = $this->input->post('account_add_account');
        $account_funds_transfer = $this->input->post('account_funds_transfer');
        $account_edit = $this->input->post('account_edit');
        $allowed_cash_account_ids = $this->input->post('allowed_cash_account_ids');
        $allowed_bank_account_ids = $this->input->post('allowed_bank_account_ids');
        $funds_transfer_account_ids = $this->input->post('funds_transfer_account_ids');
        $account_details_pettycash_ids = $this->input->post('account_details_pettycash_ids');


        //PETTY CASH
        $pettycash_sidebar = $this->input->post('pettycash_sidebar');
        $add_pettycash = $this->input->post('add_pettycash');
        $change_pettycash = $this->input->post('change_pettycash');
        $pettycash_funds_trasfer = $this->input->post('pettycash_funds_trasfer');
        $cash_request = $this->input->post('cash_request');
        $cash_approval = $this->input->post('cash_approval');
        $is_carrier = $this->input->post('is_carrier');


        $petty_cash_users = $this->input->post('petty_cash_users');
        if($petty_cash_users!='')
        {
            $petty_cash_users =  implode(",", $petty_cash_users);
        }


        $this->db->set('pettycash_sidebar', $pettycash_sidebar);
        $this->db->set('add_pettycash', $add_pettycash);
        $this->db->set('change_pettycash', $change_pettycash);
        $this->db->set('pettycash_funds_trasfer', $pettycash_funds_trasfer);
        $this->db->set('petty_cash_users', $petty_cash_users);
        $this->db->set('cash_request', $cash_request);
        $this->db->set('cash_approval', $cash_approval);
        $this->db->set('is_carrier', $is_carrier);


        //ATTENDENCE
        $attendence_sidebar = $this->input->post('attendence_sidebar');
        $attendence_add_attendence = $this->input->post('attendence_add_attendence');
        $attendence_all_attendence = $this->input->post('attendence_all_attendence');
        $attendence_add_types = $this->input->post('attendence_add_types');
        if($attendence_add_types!='')
        {
            $attendence_add_types =  implode(",", $attendence_add_types);
        }


        //DEPARTMENT
        $department_sidebar = $this->input->post('department_sidebar');
        $department_add_department = $this->input->post('department_add_department');
        $department_all_department = $this->input->post('department_all_department');
        $department_edit_department = $this->input->post('department_edit_department');
        $department_delete_department = $this->input->post('department_delete_department');

        //DESIGNATION
        $designation_sidebar = $this->input->post('designation_sidebar');
        $designation_add_designation = $this->input->post('designation_add_designation');
        $designation_all_designation = $this->input->post('designation_all_designation');
        $designation_edit_designation = $this->input->post('designation_edit_designation');
        $designation_delete_designation = $this->input->post('designation_delete_designation');

        //STAFF TYPE
        $staff_type_sidebar = $this->input->post('staff_type_sidebar');
        $staff_type_add_staff_type = $this->input->post('staff_type_add_staff_type');
        $staff_type_all_staff_type = $this->input->post('staff_type_all_staff_type');
        $staff_type_edit_staff_type = $this->input->post('staff_type_edit_staff_type');
        $staff_type_delete_staff_type = $this->input->post('staff_type_delete_staff_type');


        $recovery_portal = $this->input->post('recovery_portal');
        $all_users_recovery = $this->input->post('all_users_recovery');
        $salary = $this->input->post('salary');
        $loans = $this->input->post('loans');
        $leave_approval = $this->input->post('leave_approval');
        $loan_approval_accounts = $this->input->post('loan_approval_accounts');

        //STAFF STATUS
        $staff_sidebar = $this->input->post('staff_sidebar');
        $staff_add = $this->input->post('staff_add');
        $staff_all = $this->input->post('staff_all');
        $staff_edit = $this->input->post('staff_edit');
        $staff_upload_documents = $this->input->post('staff_upload_documents');
        $staff_delete = $this->input->post('staff_delete');
        $staff_attendence = $this->input->post('staff_attendence');
        //CLASSES STATUS
        $class_sidebar = $this->input->post('class_sidebar');
        $class_add = $this->input->post('class_add');
        $class_all = $this->input->post('class_all');
        $class_edit = $this->input->post('class_edit');
        $class_delete = $this->input->post('class_delete');
        //REPORTS STATUS
        $reports_sidebar = $this->input->post('reports_sidebar');
        $reports_student_fee_problem = $this->input->post('reports_student_fee_problem');
        $all_struckofstudent_report = $this->input->post('all_struckofstudent_report');
        $reports_discount_report = $this->input->post('reports_discount_report');
        $agent_view_statement = $this->input->post('agent_view_statement');
        $agent_view_statement_coo = $this->input->post('agent_view_statement_coo');
        $student_backup_report = $this->input->post('student_backup_report');
        //SUBJECT STATUS
        /*$subject_sidebar = $this->input->post('subject_sidebar');
        $subject_add = $this->input->post('subject_add');
        $subject_all = $this->input->post('subject_all');
        $subject_edit = $this->input->post('subject_edit');
        $subject_delete = $this->input->post('subject_delete');*/
        //STUDENTS STATUS
        $student_sidebar = $this->input->post('student_sidebar');
        $student_add = $this->input->post('student_add');
        $student_all = $this->input->post('student_all');
        $student_struck_off_list = $this->input->post('student_struck_off_list');
        $student_edit = $this->input->post('student_edit');
        $student_delete = $this->input->post('student_delete');
        $student_upload_documents = $this->input->post('student_upload_documents');
        $student_payments = $this->input->post('student_payments');
        $student_payment_reset = $this->input->post('student_payment_reset');
        $student_payment_edit = $this->input->post('student_payment_edit');
        $student_payment_delete = $this->input->post('student_payment_delete');
        $student_college_card = $this->input->post('student_college_card');
        $student_issue_refund = $this->input->post('student_issue_refund');
        $can_student_struckof = $this->input->post('can_student_struckof');
        $update_fee_submission = $this->input->post('update_fee_submission');
        $delete_users_payment = $this->input->post('delete_users_payment');
        $fine_remove = $this->input->post('fine_remove');
        $discount_reversal = $this->input->post('discount_reversal');
        $installment_date = $this->input->post('installment_date');
        $extra_fee_access = $this->input->post('extra_fee_access');


        //CONTARCTOR STATUS
        $contractor_sidebar = $this->input->post('contractor_sidebar');
        $contract_sidebar = $this->input->post('contract_sidebar');
        $contractor_add = $this->input->post('contractor_add');
        $contractor_all = $this->input->post('contractor_all');
        $contractor_edit = $this->input->post('contractor_edit');
        $contractor_delete = $this->input->post('contractor_delete');
        $contractor_payments = $this->input->post('contractor_payments');
        $contractor_payment_reset = $this->input->post('contractor_payment_reset');
        //VISITORS STATUS
        $visitor_sidebar = $this->input->post('visitor_sidebar');
        $visitor_add = $this->input->post('visitor_add');
        $visitor_all = $this->input->post('visitor_all');
        $visitor_edit = $this->input->post('visitor_edit');
        $visitor_delete = $this->input->post('visitor_delete');
        //ARCHIVE STATUS
        $archive_sidebar = $this->input->post('archive_sidebar');
        $define_allownces = $this->input->post('define_allownces');

        //FEE DUES STATUS
        $fee_due_sidebar = $this->input->post('fee_due_sidebar');
        //EXPENSE STATUS
        $expense_sidebar = $this->input->post('expense_sidebar');
        $expense_add = $this->input->post('expense_add');
        $expense_add_mobile = $this->input->post('expense_add_mobile');
        $expense_second_approval = $this->input->post('expense_second_approval');
        $expense_all = $this->input->post('expense_all');
        $expense_edit = $this->input->post('expense_edit');
        $expense_delete = $this->input->post('expense_delete');
        $expense_approval = $this->input->post('expense_approval');
        $expense_category = $this->input->post('expense_category');
        $expense_view_user = $this->input->post('expense_view_user');
        $expense_no_of_days = $this->input->post('expense_no_of_days');
        $expense_campus_ids = $this->input->post('expense_campus_ids');
        $expense_advertisement_create = $this->input->post('expense_advertisement_create');
        $expense_advertisement_approval = $this->input->post('expense_advertisement_approval');

        if($expense_campus_ids!='')
        {
            $expense_campus_ids =  implode(",", $expense_campus_ids);
        }

        $purchase_campuses = $this->input->post('purchase_campuses');
        $is_purchaser = $this->input->post('is_purchaser');

        if($purchase_campuses!='')
        {
            $purchase_campuses =  implode(",", $purchase_campuses);
        }

        //STUDENT PERFORMANCE STATUS
        $student_performance_sidebar = $this->input->post('student_performance_sidebar');
        //HOLIDAYS STATUS
        $holidays_sidebar = $this->input->post('holidays_sidebar');
        //SUPPLY STUDENTS STATUS
        $supply_students_sidebar = $this->input->post('supply_students_sidebar');
        //COUNCIL LIST STATUS
        $council_list_sidebar = $this->input->post('council_list_sidebar');
        $create_council_list = $this->input->post('create_council_list');
        $create_council_list_with_fee = $this->input->post('create_council_list_with_fee');
        //WEBSITE MANAGEMENT
        $event_images = $this->input->post('event_images');
        $slider_images = $this->input->post('slider_images');
        $news_updates = $this->input->post('news_updates');
        $campuses = $this->input->post('campuses');
        $sms = $this->input->post('sms');
        $dailybankclosing = $this->input->post('dailybankclosing');
        $download_documents = $this->input->post('download_documents');
        //PUNJAB PHARMACY COUNCIL
        $punjab_pharmacy_council_access = $this->input->post('punjab_pharmacy_council_access');
        $enter_punjab_council_roll_no = $this->input->post('enter_punjab_council_roll_no');
        $enter_punjab_council_result = $this->input->post('enter_punjab_council_result');
        $final_result_pharmacy_technician = $this->input->post('final_result_pharmacy_technician');
        $add_council_fee = $this->input->post('add_council_fee');
        $next_exam_status = $this->input->post('next_exam_status');
        //NEXT COUNCIL ADMISSION ACCESS
        $next_council_admission_access = $this->input->post('next_council_admission_access');
        //COURSE MANAGEMENT
        $course_management_access = $this->input->post('course_management_access');
        $course_management_add_course = $this->input->post('course_management_add_course');
        $course_management_all_course = $this->input->post('course_management_all_course');
        $course_management_edit_course = $this->input->post('course_management_edit_course');
        $course_management_delete_course = $this->input->post('course_management_delete_course');
        $course_management_add_subject = $this->input->post('course_management_add_subject');
        $course_management_all_subject = $this->input->post('course_management_all_subject');
        $course_management_edit_subject = $this->input->post('course_management_edit_subject');
        $course_management_delete_subject = $this->input->post('course_management_delete_subject');
        $course_management_add_chapter = $this->input->post('course_management_add_chapter');
        $course_management_all_chapter = $this->input->post('course_management_all_chapter');
        $course_management_edit_chapter = $this->input->post('course_management_edit_chapter');
        $course_management_delete_chapter = $this->input->post('course_management_delete_chapter');
        $course_management_add_topic = $this->input->post('course_management_add_topic');
        $course_management_all_topic = $this->input->post('course_management_all_topic');
        $course_management_edit_topic = $this->input->post('course_management_edit_topic');
        $course_management_delete_topic = $this->input->post('course_management_delete_topic');
        //TEST ENGINE
        $test_engine_sidebar = $this->input->post('test_engine_sidebar');
        $test_engine_add_practical_books = $this->input->post('test_engine_add_practical_books');
        $test_engine_add_practical = $this->input->post('test_engine_add_practical');
        $test_engine_edit_practical = $this->input->post('test_engine_edit_practical');
        $test_engine_delete_practical = $this->input->post('test_engine_delete_practical');
        $test_engine_books = $this->input->post('test_engine_books');
        $test_engine_view_question = $this->input->post('test_engine_view_question');
        $test_engine_add_questions = $this->input->post('test_engine_add_questions');
        $test_engine_edit_question = $this->input->post('test_engine_edit_question');
        $test_engine_delete_question = $this->input->post('test_engine_delete_question');
        /*$test_engine_courses = $this->input->post('test_engine_courses');
        $test_engine_subjects = $this->input->post('test_engine_subjects');
        $test_engine_topics_questions = $this->input->post('test_engine_topics_questions');*/
        $subject_ids = $this->input->post('subject_ids');
        if($subject_ids!='')
        {
            $subject_ids =  implode(",", $subject_ids);
        }
        $test_engine_books = $this->input->post('test_engine_books');

        //PAPERS & RESULTS
        $papers_results_sidebar = $this->input->post('papers_results_sidebar');
        $papers_results_add_paper = $this->input->post('papers_results_add_paper');
        $papers_results_all_paper = $this->input->post('papers_results_all_paper');
        $papers_results_view_paper = $this->input->post('papers_results_view_paper');
        $papers_results_add_result = $this->input->post('papers_results_add_result');
        $papers_results_student_results = $this->input->post('papers_results_student_results');
        $test_system = $this->input->post('test_system');
        $improvement_report = $this->input->post('improvement_report');

        //SCHEDULE MANAGEMENT TIME TABLE
        $schedule_management_sidebar = $this->input->post('schedule_management_sidebar');
        $syllabus_sidebar = $this->input->post('syllabus_sidebar');
        $make_lecture = $this->input->post('make_lecture');
        $all_lecture = $this->input->post('all_lecture');
        $session_wise_syllabus = $this->input->post('session_wise_syllabus');
        $timetable_sidebar = $this->input->post('timetable_sidebar');
        $study_type = $this->input->post('study_type');
        $shifts = $this->input->post('shifts');
        $add_timetable = $this->input->post('add_timetable');
        $view_timetable = $this->input->post('view_timetable');

        //ASSIGNMENTS
        $assignments_sidebar = $this->input->post('assignments_sidebar');
        $assignments_add_assignment = $this->input->post('assignments_add_assignment');
        $assignments_all_assignments = $this->input->post('assignments_all_assignments');
        $assignments_uncheck_assignments = $this->input->post('assignments_uncheck_assignments');
        $assignments_check_assignments = $this->input->post('assignments_check_assignments');

        //ASSIGNMENT
        $assignment_subject_ids = $this->input->post('assignment_subject_ids');
        if($assignment_subject_ids!='')
        {
            $assignment_subject_ids =  implode(",", $assignment_subject_ids);
        }

        //HR
        $hr_sidebar = $this->input->post('hr_sidebar');
        $hr_add_interview = $this->input->post('hr_add_interview');
        $hr_edit_interview = $this->input->post('hr_edit_interview');
        $hr_delete_interview = $this->input->post('hr_delete_interview');
        $hr_all_interview = $this->input->post('hr_all_interview');
        //REMINDERS
        $reminders_sidebar = $this->input->post('reminders_sidebar');
        $reminders_add_rules = $this->input->post('reminders_add_rules');
        $reminders_all_rules = $this->input->post('reminders_all_rules');
        $reminders_all_pending = $this->input->post('reminders_all_pending');
        $reminders_all_completed = $this->input->post('reminders_all_completed');
        //DOCUMENTS
        $documents_access = $this->input->post('documents_access');
        $documents_diploma = $this->input->post('documents_diploma');
        $documents_students = $this->input->post('documents_students');
        //CLASS MANAGEMENT
        $class_ids = $this->input->post('class_ids');
        if($class_ids!='')
        {
            $class_ids =  implode(",", $class_ids);
        }
        //CAMPUS MANAGEMENT
        $campus_ids = $this->input->post('campus_ids');
        if($campus_ids!='')
        {
            $campus_ids =  implode(",", $campus_ids);
        }
        //FEE DUES CAMPUS MANAGEMENT
        $fee_dues_campus_ids = $this->input->post('fee_dues_campus_ids');
        if($fee_dues_campus_ids!='')
        {
            $fee_dues_campus_ids =  implode(",", $fee_dues_campus_ids);
        }
        //FEE RECOVERY CLASSES ACCESS
        $fee_recovery_class_ids = $this->input->post('fee_recovery_class_ids');
        if($fee_recovery_class_ids!='')
        {
            $fee_recovery_class_ids = implode(",", $fee_recovery_class_ids);
        }
        //CITIES
        $cities = $this->input->post('cities');
        if($cities!='')
        {
            $cities =  implode(",", $cities);
        }
        //CITIES

        if($campus_closing_ids!='')
        {
            $campus_closing_ids =  implode(",", $campus_closing_ids);
        }
        if($allowed_cash_account_ids!='')
        {
            $allowed_cash_account_ids = implode(",", $allowed_cash_account_ids);
        }
        if($allowed_bank_account_ids!='')
        {
            $allowed_bank_account_ids = implode(",", $allowed_bank_account_ids);
        }
        if($funds_transfer_account_ids!='')
        {
            $funds_transfer_account_ids = implode(",", $funds_transfer_account_ids);
        }
        if($account_details_pettycash_ids!='')
        {
            $account_details_pettycash_ids = implode(",", $account_details_pettycash_ids);
        }
        $other_cities_access = $this->input->post('other_cities_access');


        $attendance_mobile_report = $this->input->post('attendance_mobile_report');
        $session_students_mobile_report = $this->input->post('session_students_mobile_report');
        $how_to_use = $this->input->post('how_to_use');

        //INVENTORY
        $inventory = $this->input->post('inventory');
        $add_vendor = $this->input->post('add_vendor');
        $manage_vendor = $this->input->post('manage_vendor');
        $edit_vendor = $this->input->post('edit_vendor');
        $delete_vendor = $this->input->post('delete_vendor');
        $add_purchase_request = $this->input->post('add_purchase_request');
        $all_purchase_request = $this->input->post('all_purchase_request');
        $edit_purchase_request = $this->input->post('edit_purchase_request');
        $delete_purchase_request = $this->input->post('delete_purchase_request');
        $add_qoutation = $this->input->post('add_qoutation');
        $approve_qoutation = $this->input->post('approve_qoutation');
        $purchase_orders = $this->input->post('purchase_orders');
        $grn_gate_approval = $this->input->post('grn_gate_approval');
        $grn_approval = $this->input->post('grn_approval');
        $add_room = $this->input->post('add_room');
        $all_room = $this->input->post('all_room');
        $edit_room = $this->input->post('edit_room');
        $delete_room = $this->input->post('delete_room');
        $add_subroom = $this->input->post('add_subroom');
        $all_subroom = $this->input->post('all_subroom');
        $edit_subroom = $this->input->post('edit_subroom');
        $delete_subroom = $this->input->post('delete_subroom');
        $manage_product_names = $this->input->post('manage_product_names');
        $manage_document_names = $this->input->post('manage_document_names');
        $add_product = $this->input->post('add_product');
        $all_product = $this->input->post('all_product');
        $add_product_issue_request = $this->input->post('add_product_issue_request');
        $all_product_issue_request = $this->input->post('all_product_issue_request');
        $manage_gin = $this->input->post('manage_gin');
        $manage_grn = $this->input->post('manage_grn');
        $generate_qrs = $this->input->post('generate_qrs');
        $product_return_request = $this->input->post('product_return_request');
        $approve_product_return_request = $this->input->post('approve_product_return_request');
        $inventory_campuses = $this->input->post('inventory_campuses');
        if($inventory_campuses!='')
        {
            $inventory_campuses =  implode(",", $inventory_campuses);
        }
        $product_request_approval_campuses = $this->input->post('product_request_approval_campuses');
        if($product_request_approval_campuses!='')
        {
            $product_request_approval_campuses =  implode(",", $product_request_approval_campuses);
        }
        
        $council_report = $this->input->post('council_report');
        $council_report_add_information_can_add_fee = $this->input->post('council_report_add_information_can_add_fee');
        $council_report_add_information_can_add_expense = $this->input->post('council_report_add_information_can_add_expense');
        
        
        $council_report_colleges = $this->input->post('council_report_colleges');
        if($council_report_colleges!='')
        {
            $council_report_colleges =  implode(",", $council_report_colleges);
        }
        
        $council_report_courses = $this->input->post('council_report_courses');
        if($council_report_courses!='')
        {
            $council_report_courses =  implode(",", $council_report_courses);
        }
        
        $apply_loan = $this->input->post('apply_loan');
        $this->db->set('apply_loan', $apply_loan);
        $payroll_statutory_rules = $this->input->post('payroll_statutory_rules');
        $payroll_income_tax_rules = $this->input->post('payroll_income_tax_rules');
        $this->db->set('payroll_statutory_rules', $payroll_statutory_rules);
        $this->db->set('payroll_income_tax_rules', $payroll_income_tax_rules);
        
        $this->db->set('council_report_colleges', $council_report_colleges);
        $this->db->set('council_report_courses', $council_report_courses);


        $this->db->set('dashboard_total_student_box', $dashboard_total_student_box);
        $this->db->set('dashboard_total_teacher_box', $dashboard_total_teacher_box);
        $this->db->set('dashboard_new_admission', $dashboard_new_admission);
        $this->db->set('dashboard_month_earning', $dashboard_month_earning);
        $this->db->set('dashboard_month_expense', $dashboard_month_expense);
        $this->db->set('dashboard_month_profit', $dashboard_month_profit);
        $this->db->set('dashboard_fee_status', $dashboard_fee_status);
        $this->db->set('dashboard_classes_status', $dashboard_classes_status);
        $this->db->set('dashboard_update_payment_box', $dashboard_update_payment_box);
        $this->db->set('dashboard_update_discount_box', $dashboard_update_discount_box);
        $this->db->set('dashboard_update_student_box', $dashboard_update_student_box);
        $this->db->set('dashboard_check_student_box', $dashboard_check_student_box);
        $this->db->set('dashboard_campus_status_box', $dashboard_campus_status_box);
        $this->db->set('dashboard_new_admisssion_entries_box', $dashboard_new_admisssion_entries_box);
        $this->db->set('dashboard_new_expense_entries_box', $dashboard_new_expense_entries_box);
        $this->db->set('dashboard_students_due_fees', $dashboard_students_due_fees);
        $this->db->set('dashboard_students_due_fees_status', $dashboard_students_due_fees_status);
        $this->db->set('dashboard_reminders_status', $dashboard_reminders_status);
        $this->db->set('dashboard_test_engine_questions', $dashboard_test_engine_questions);
        $this->db->set('dashboard_uncheck_assignment', $dashboard_uncheck_assignment);
        $this->db->set('dashboard_update_reversal_payment_box', $dashboard_update_reversal_payment_box);
        $this->db->set('dashboard_students_fees_reversal', $dashboard_students_fees_reversal);
        $this->db->set('how_to_use', $how_to_use);

        $this->db->set('online_application_access', $online_application_access);
        $this->db->set('online_application_new_admissions', $online_application_new_admissions);
        $this->db->set('online_application_checked_admissions', $online_application_checked_admissions);
        $this->db->set('online_application_all', $online_application_all);
        $this->db->set('facebook_leads', $facebook_leads);

        $this->db->set('attendence_sidebar', $attendence_sidebar);
        $this->db->set('attendence_add_attendence', $attendence_add_attendence);
        $this->db->set('attendence_all_attendence', $attendence_all_attendence);
        $this->db->set('attendence_add_types', $attendence_add_types);

        $this->db->set('accounts_sidebar', $accounts_sidebar);
        $this->db->set('account_details', $account_details);
        $this->db->set('profit_distribution', $profit_distribution);
        $this->db->set('campus_petty_cash', $campus_petty_cash);
        $this->db->set('advance_system', $advance_system);
        $this->db->set('dailyclosing', $dailyclosing);
        $this->db->set('accounts', $accounts);
        $this->db->set('closing_reconcile', $closing_reconcile);
        $this->db->set('closing_conciliation_edit', $closing_conciliation_edit);
        $this->db->set('bank_reconciliation', $bank_reconciliation);
        $this->db->set('view_campus_closings', $view_campus_closings);
        $this->db->set('campus_closing_ids', $campus_closing_ids);
        $this->db->set('closing_amount_edit', $closing_amount_edit);
        $this->db->set('closing_coo', $closing_coo);
        $this->db->set('misc_income', $misc_income);
        $this->ensureAccountDetailsAccessColumns();
        $this->db->set('account_add_account', $account_add_account);
        $this->db->set('account_funds_transfer', $account_funds_transfer);
        $this->db->set('account_edit', $account_edit);
        $this->db->set('allowed_cash_account_ids', $allowed_cash_account_ids);
        $this->db->set('allowed_bank_account_ids', $allowed_bank_account_ids);
        $this->db->set('funds_transfer_account_ids', $funds_transfer_account_ids);
        $this->db->set('account_details_pettycash_ids', $account_details_pettycash_ids);

        $this->db->set('pettycash_sidebar', $pettycash_sidebar);
        $this->db->set('add_pettycash', $add_pettycash);
        $this->db->set('change_pettycash', $change_pettycash);
        $this->db->set('pettycash_funds_trasfer', $pettycash_funds_trasfer);
        $this->db->set('petty_cash_users', $petty_cash_users);

        $this->db->set('department_sidebar', $department_sidebar);
        $this->db->set('department_add_department', $department_add_department);
        $this->db->set('department_all_department', $department_all_department);
        $this->db->set('department_edit_department', $department_edit_department);
        $this->db->set('department_delete_department', $department_delete_department);

        $this->db->set('designation_sidebar', $designation_sidebar);
        $this->db->set('designation_add_designation', $designation_add_designation);
        $this->db->set('designation_all_designation', $designation_all_designation);
        $this->db->set('designation_edit_designation', $designation_edit_designation);
        $this->db->set('designation_delete_designation', $designation_delete_designation);

        $this->db->set('staff_type_sidebar', $staff_type_sidebar);
        $this->db->set('staff_type_add_staff_type', $staff_type_add_staff_type);
        $this->db->set('staff_type_all_staff_type', $staff_type_all_staff_type);
        $this->db->set('staff_type_edit_staff_type', $staff_type_edit_staff_type);
        $this->db->set('staff_type_delete_staff_type', $staff_type_delete_staff_type);

        $this->db->set('recovery_portal', $recovery_portal);
        $this->db->set('all_users_recovery', $all_users_recovery);
        $this->db->set('define_allownces', $define_allownces);
        $this->db->set('salary', $salary);
        $this->db->set('loan_approval', $loans);
        $this->db->set('leave_approval', $leave_approval);
        $this->db->set('loan_approval_accounts', $loan_approval_accounts);

        $this->db->set('staff_sidebar', $staff_sidebar);
        $this->db->set('staff_add', $staff_add);
        $this->db->set('staff_all', $staff_all);
        $this->db->set('staff_edit', $staff_edit);
        $this->db->set('staff_upload_documents', $staff_upload_documents);
        $this->db->set('staff_delete', $staff_delete);
        $this->db->set('staff_attendence', $staff_attendence);

        $this->db->set('class_sidebar', $class_sidebar);
        $this->db->set('class_add', $class_add);
        $this->db->set('class_all', $class_all);
        $this->db->set('class_edit', $class_edit);
        $this->db->set('class_delete', $class_delete);

        $this->db->set('reports_sidebar', $reports_sidebar);
        $this->db->set('reports_student_fee_problem', $reports_student_fee_problem);		$this->db->set('all_struckofstudent_report', $all_struckofstudent_report);
        $this->ensureReportAccessColumns();
        $this->db->set('reports_discount_report', $reports_discount_report);


        $this->db->set('student_sidebar', $student_sidebar);
        $this->db->set('student_add', $student_add);
        $this->db->set('student_all', $student_all);
        $this->db->set('student_struck_off_list', $student_struck_off_list);
        $this->db->set('student_edit', $student_edit);
        $this->db->set('student_delete', $student_delete);
        $this->db->set('student_upload_documents', $student_upload_documents);
        $this->db->set('student_payments', $student_payments);
        $this->db->set('student_payment_reset', $student_payment_reset);
        $this->db->set('student_payment_edit', $student_payment_edit);
        $this->db->set('student_payment_delete', $student_payment_delete);
        $this->db->set('student_college_card', $student_college_card);
        $this->db->set('student_issue_refund', $student_issue_refund);
        $this->db->set('can_student_struckof', $can_student_struckof);
        $this->db->set('update_fee_submission', $update_fee_submission);
        $this->db->set('delete_users_payment', $delete_users_payment);
        $this->db->set('fine_remove', $fine_remove);
        $this->db->set('discount_reversal', $discount_reversal);
        $this->db->set('installment_date', $installment_date);
        $this->db->set('extra_fee_access', $extra_fee_access);
        $fee_by_cash = $this->input->post('fee_by_cash');
        $fee_by_bank = $this->input->post('fee_by_bank');
        $fee_by_paypro = $this->input->post('fee_by_paypro');
        $this->db->set('fee_by_cash', $fee_by_cash);
        $this->db->set('fee_by_bank', $fee_by_bank);
        $this->db->set('fee_by_paypro', $fee_by_paypro);
        $change_exam_no_in_payments = $this->input->post('change_exam_no_in_payments');
        $this->db->set('change_exam_no_in_payments', $change_exam_no_in_payments);
        $council_list_report = $this->input->post('council_list_report');
        $this->db->set('council_list_report', $council_list_report);

        $receipt_book = $this->input->post('receipt_book');
        $computer_challan = $this->input->post('computer_challan');
        $archived_students = $this->input->post('archived_students');

        $this->db->set('receipt_book', $receipt_book);
        $this->db->set('computer_challan', $computer_challan);
        $this->db->set('archived_students', $archived_students);


        $this->db->set('contractor_sidebar', $contractor_sidebar);
        $this->db->set('contract_sidebar', $contract_sidebar);
        $this->db->set('contractor_add', $contractor_add);
        $this->db->set('contractor_all', $contractor_all);
        $this->db->set('contractor_edit', $contractor_edit);
        $this->db->set('contractor_delete', $contractor_delete);
        $this->db->set('contractor_payments', $contractor_payments);
        $this->db->set('contractor_payment_reset', $contractor_payment_reset);

        $this->db->set('visitor_sidebar', $visitor_sidebar);
        $this->db->set('visitor_add', $visitor_add);
        $this->db->set('visitor_all', $visitor_all);
        $this->db->set('visitor_edit', $visitor_edit);
        $this->db->set('visitor_delete', $visitor_delete);

        $this->db->set('archive_sidebar', $archive_sidebar);

        $this->db->set('attendence_sidebar', $attendence_sidebar);

        $this->db->set('fee_due_sidebar', $fee_due_sidebar);

        $this->db->set('expense_sidebar', $expense_sidebar);
        $this->db->set('expense_add', $expense_add);
        $this->db->set('expense_add_mobile', $expense_add_mobile);
        $this->db->set('expense_second_approval', $expense_second_approval);
        $this->db->set('expense_all', $expense_all);
        $this->db->set('expense_edit', $expense_edit);
        $this->db->set('expense_delete', $expense_delete);
        $this->db->set('expense_approval', $expense_approval);
        $this->db->set('expense_category', $expense_category);
        $this->db->set('expense_campus_ids', $expense_campus_ids);
        $this->db->set('expense_view_user', $expense_view_user);
        $this->db->set('expense_no_of_days', $expense_no_of_days);
        $this->db->set('expense_advertisement_approval', $expense_advertisement_approval);
        $this->db->set('expense_advertisement_create', $expense_advertisement_create);

        $this->db->set('student_performance_sidebar', $student_performance_sidebar);

        $this->db->set('supply_students_sidebar', $supply_students_sidebar);

        $this->db->set('holidays_sidebar', $holidays_sidebar);

        $this->db->set('council_list_sidebar', $council_list_sidebar);
        $this->db->set('create_council_list', $create_council_list);
        $this->db->set('create_council_list_with_fee', $create_council_list_with_fee);

        $this->db->set('event_images', $event_images);
        $this->db->set('slider_images', $slider_images);
        $this->db->set('news_updates', $news_updates);
        $this->db->set('campuses', $campuses);
        $this->db->set('sms', $sms);
        $this->db->set('download_documents', $download_documents);

        $this->db->set('punjab_pharmacy_council_access', $punjab_pharmacy_council_access);
        $this->db->set('enter_punjab_council_roll_no', $enter_punjab_council_roll_no);
        $this->db->set('enter_punjab_council_result', $enter_punjab_council_result);
        $this->db->set('final_result_pharmacy_technician', $final_result_pharmacy_technician);
        $this->db->set('add_council_fee', $add_council_fee);
        $this->db->set('next_exam_status', $next_exam_status);

        $this->db->set('next_council_admission_access', $next_council_admission_access);

        $this->db->set('course_management_access', $course_management_access);
        $this->db->set('course_management_add_course', $course_management_add_course);
        $this->db->set('course_management_all_course', $course_management_all_course);
        $this->db->set('course_management_edit_course', $course_management_edit_course);
        $this->db->set('course_management_delete_course', $course_management_delete_course);
        $this->db->set('course_management_add_subject', $course_management_add_subject);
        $this->db->set('course_management_all_subject', $course_management_all_subject);
        $this->db->set('course_management_edit_subject', $course_management_edit_subject);
        $this->db->set('course_management_delete_subject', $course_management_delete_subject);
        $this->db->set('course_management_add_chapter', $course_management_add_chapter);
        $this->db->set('course_management_all_chapter', $course_management_all_chapter);
        $this->db->set('course_management_edit_chapter', $course_management_edit_chapter);
        $this->db->set('course_management_delete_chapter', $course_management_delete_chapter);
        $this->db->set('course_management_add_topic', $course_management_add_topic);
        $this->db->set('course_management_all_topic', $course_management_all_topic);
        $this->db->set('course_management_edit_topic', $course_management_edit_topic);
        $this->db->set('course_management_delete_topic', $course_management_delete_topic);

        $this->db->set('test_engine_sidebar', $test_engine_sidebar);
        $this->db->set('test_engine_add_practical_books', $test_engine_add_practical_books);
        $this->db->set('test_engine_add_practical', $test_engine_add_practical);
        $this->db->set('test_engine_edit_practical', $test_engine_edit_practical);
        $this->db->set('test_engine_delete_practical', $test_engine_delete_practical);
        $this->db->set('test_engine_books', $test_engine_books);
        $this->db->set('test_engine_view_question', $test_engine_view_question);
        $this->db->set('test_engine_add_questions', $test_engine_add_questions);
        $this->db->set('test_engine_edit_question', $test_engine_edit_question);
        $this->db->set('test_engine_delete_question', $test_engine_delete_question);
        $this->db->set('test_engine_subject_ids', $subject_ids);
        $this->db->set('test_engine_books', $test_engine_books);

        $this->db->set('papers_results_sidebar', $papers_results_sidebar);
        $this->db->set('papers_results_add_paper', $papers_results_add_paper);
        $this->db->set('papers_results_all_paper', $papers_results_all_paper);
        $this->db->set('papers_results_view_paper', $papers_results_view_paper);
        $this->db->set('papers_results_add_result', $papers_results_add_result);
        $this->db->set('papers_results_student_results', $papers_results_student_results);
        $this->db->set('test_system', $test_system);
        $this->db->set('improvement_report', $improvement_report);

        $this->db->set('schedule_management_sidebar', $schedule_management_sidebar);
        $this->db->set('syllabus_sidebar', $syllabus_sidebar);
        $this->db->set('make_lecture', $make_lecture);
        $this->db->set('all_lecture', $all_lecture);
        $this->db->set('session_wise_syllabus', $session_wise_syllabus);
        $this->db->set('timetable_sidebar', $timetable_sidebar);
        $this->db->set('study_type', $study_type);
        $this->db->set('shifts', $shifts);
        $this->db->set('add_timetable', $add_timetable);
        $this->db->set('view_timetable', $view_timetable);

        $this->db->set('assignments_sidebar', $assignments_sidebar);
        $this->db->set('assignments_add_assignment', $assignments_add_assignment);
        $this->db->set('assignments_all_assignments', $assignments_all_assignments);
        $this->db->set('assignments_uncheck_assignments', $assignments_uncheck_assignments);
        $this->db->set('assignments_check_assignments', $assignments_check_assignments);

        $this->db->set('assignment_subject_ids', $assignment_subject_ids);

        $this->db->set('hr_sidebar', $hr_sidebar);
        $this->db->set('hr_add_interview', $hr_add_interview);
        $this->db->set('hr_edit_interview', $hr_edit_interview);
        $this->db->set('hr_delete_interview', $hr_delete_interview);
        $this->db->set('hr_all_interview', $hr_all_interview);

        $this->db->set('reminders_sidebar', $reminders_sidebar);
        $this->db->set('reminders_add_rules', $reminders_add_rules);
        $this->db->set('reminders_all_rules', $reminders_all_rules);
        $this->db->set('reminders_all_pending', $reminders_all_pending);
        $this->db->set('reminders_all_completed', $reminders_all_completed);

        $this->db->set('documents_access', $documents_access);
        $this->db->set('documents_diploma', $documents_diploma);
        $this->db->set('documents_students', $documents_students);

        $this->db->set('class_ids', $class_ids);
        $this->db->set('dailybankclosing', $dailybankclosing);

        $this->db->set('campus_ids', $campus_ids);

        $this->db->set('fee_dues_campus_ids', $fee_dues_campus_ids);

        $this->db->set('fee_recovery_class_ids', $fee_recovery_class_ids);

        $this->db->set('cities', $cities);
        $this->db->set('other_cities_access',$other_cities_access);
        $this->db->set('is_purchaser', $is_purchaser);
        $this->db->set('purchase_campuses', $purchase_campuses);

        $this->db->set('attendance_mobile_report', $attendance_mobile_report);
        $this->db->set('session_students_mobile_report', $session_students_mobile_report);
        $this->db->set('agent_view_statement', $agent_view_statement);
        $this->db->set('agent_view_statement_coo', $agent_view_statement_coo);
        $this->db->set('student_backup_report', $student_backup_report);

        $this->db->set('inventory',$inventory);
        $this->db->set('add_vendor',$add_vendor);
        $this->db->set('manage_vendor',$manage_vendor);
        $this->db->set('edit_vendor',$edit_vendor);
        $this->db->set('delete_vendor',$delete_vendor);
        $this->db->set('add_purchase_request',$add_purchase_request);
        $this->db->set('all_purchase_request',$all_purchase_request);
        $this->db->set('edit_purchase_request',$edit_purchase_request);
        $this->db->set('delete_purchase_request',$delete_purchase_request);
        $this->db->set('add_qoutation',$add_qoutation);
        $this->db->set('approve_qoutation',$approve_qoutation);
        $this->db->set('purchase_orders',$purchase_orders);
        $this->db->set('grn_gate_approval',$grn_gate_approval);
        $this->db->set('grn_approval',$grn_approval);
        $this->db->set('add_room',$add_room);
        $this->db->set('all_room',$all_room);
        $this->db->set('edit_room',$edit_room);
        $this->db->set('delete_room',$delete_room);
        $this->db->set('add_subroom',$add_subroom);
        $this->db->set('all_subroom',$all_subroom);
        $this->db->set('edit_subroom',$edit_subroom);
        $this->db->set('delete_subroom',$delete_subroom);
        $this->db->set('manage_product_names',$manage_product_names);
        $this->db->set('manage_document_names',$manage_document_names);
        $this->db->set('add_product',$add_product);
        $this->db->set('all_product',$all_product);
        $this->db->set('add_product_issue_request',$add_product_issue_request);
        $this->db->set('all_product_issue_request',$all_product_issue_request);
        $this->db->set('manage_gin',$manage_gin);
        $this->db->set('manage_grn',$manage_grn);
        $this->db->set('generate_qrs',$generate_qrs);
        $this->db->set('product_return_request',$product_return_request);
        $this->db->set('approve_product_return_request',$approve_product_return_request);
        $this->db->set('inventory_campuses',$inventory_campuses);
        $this->db->set('product_request_approval_campuses',$product_request_approval_campuses);
        $this->db->set('council_report',$council_report);
        $this->db->set('council_report_add_information_can_add_fee',$council_report_add_information_can_add_fee);
        $this->db->set('council_report_add_information_can_add_expense',$council_report_add_information_can_add_expense);
        $this->setConstructionAccessFields();


        if($user_id!='')
        {
            $this->db->where('user_id', $user_id);
            $this->db->update('access');
        }
        else
        {
            $this->db->where('designation_id', $designation_id);
            $this->db->update('access_rules');
            $this->updateUsersAccess($designation_id);
        }


        /*
        if (@$pos_access == "1")
        {
            $this->saveEmployee($user_id,"0");
        }
        else{
            $this->saveEmployee($user_id,"1");
        }
        */
    }

    function saveEmployee($user_id = "",$type ="0")
    {
        $user = $this->db->get_where("users","user_id = '$user_id'")->row();
        $employee = $this->db->get_where("employees","username = '$user->username'")->result_array();

        $person_data = array(
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'gender' => $user->gender,
            'email' => $user->email,
            'phone_number' => $user->mobile,
            'address_1' => $user->address,
            'address_2' => $user->address,
            'city' => $user->city,
            'state' => "",
            'zip' => "",
            'country' => "Pakistan",
            'comments' => "Shahbaz College User"
        );
        $grants_data = ['customers', 'items', 'items_stock', 'suppliers', 'reports', 'reports_inventory'
            , 'reports_items', 'reports_sales', 'receivings', 'receivings_stock', 'sales', 'sales_stock'];

        $employee_data = array(
            'username' => $user->username,
            'password' => md5("12345"),
            'deleted' => $type
        );

        if (count($employee)>0)
        {
            if ($this->Employee->save_employee($person_data, $employee_data, $grants_data, $employee[0]['person_id'])) {
                return true;
            } else {
                return false;
            }

        }else {
            if ($this->Employee->save_employee($person_data, $employee_data, $grants_data, -1)) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getDepartments()
    {
        $query = $this->db->get('departments')->result_array();
        return $query;
    }
    
    public function updateUsersAccess($designation_id)
    {
        $users = $this->db->select('user_id, designation_id')
            ->where("FIND_IN_SET($designation_id, designation_id) !=", 0)
            ->get('users')
            ->result_array();
            
            
    
        if (empty($users)) return;
    
        $columns = $this->db->list_fields('access_rules');
        $ignore  = ['access_id', 'designation_id', 'created_at', 'updated_at'];
    
        foreach ($users as $user) {
    
            $user_id = $user['user_id'];
            $designation_ids = explode(',', $user['designation_id']);
    
            $rules = $this->db->where_in('designation_id', $designation_ids)
                ->get('access_rules')
                ->result_array();
                
    
            if (empty($rules)) continue;
    
            $final = [];
    
            foreach ($columns as $col) {
    
                if (in_array($col, $ignore)) continue;
    
                $values = array_column($rules, $col);
    
                // only remove NULL (not empty string / 0)
                $values = array_filter($values, function ($v) {
                    return $v !== null;
                });
    
                // 🔴 IMPORTANT: revoke case
                if (empty($values)) {
                    $final[$col] = null;   // access revoke
                    continue;
                }
    
                // BOOLEAN / INT → OR logic
                if (count(array_unique($values)) <= 2 && max($values) <= 1) {
                    $final[$col] = max($values);
                }
                // CSV / STRING
                else {
                    $final[$col] = $this->mergeCsv($values);
                }
            }
    
            // check access row
            $exists = $this->db->select('access_id')
                ->where('user_id', $user_id)
                ->get('access')
                ->row();
                
                
                
    
            if ($exists) {
                $this->db->where('user_id', $user_id)->update('access', $final);
            } else {
                $final['user_id'] = $user_id;
                $this->db->insert('access', $final);
                
                print_r($final);
            exit();
            }
        }
    }

    private function mergeCsv(array $values)
    {
        $merged = [];

        foreach ($values as $v) {
            if ($v !== null && $v !== '') {
                $merged = array_merge($merged, explode(',', $v));
            }
        }

        return implode(',', array_unique($merged));
    }
}
?>
