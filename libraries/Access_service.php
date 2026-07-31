<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Access management for Accessapi — mirrors legacy Access controller + Accesses model.
 */
class Access_service {

    /** @var CI_Controller */
    private $ci;

    private static $CSV_FIELDS = array(
        'campus_ids', 'class_ids', 'campus_closing_ids', 'allowed_cash_account_ids',
        'allowed_bank_account_ids', 'funds_transfer_account_ids', 'account_details_pettycash_ids',
        'petty_cash_users', 'attendence_add_types', 'expense_campus_ids', 'inventory_campuses',
        'product_request_approval_campuses', 'purchase_campuses', 'pos_campuses',
        'council_report_colleges', 'council_report_courses', 'test_engine_subject_ids',
        'assignment_subject_ids', 'fee_dues_campus_ids', 'fee_recovery_class_ids',
        'cities', 'other_cities_access',
    );

    private static $FORM_ARRAY_FIELDS = array(
        'campus_ids' => 'campus_ids',
        'online_admission_campus_ids' => 'online_admission_campus_ids',
        'campus_closing_ids' => 'campus_closing_ids',
        'allowed_cash_account_ids' => 'allowed_cash_account_ids',
        'allowed_bank_account_ids' => 'allowed_bank_account_ids',
        'funds_transfer_account_ids' => 'funds_transfer_account_ids',
        'account_details_pettycash_ids' => 'account_details_pettycash_ids',
        'petty_cash_users' => 'petty_cash_users',
        'attendence_add_types' => 'attendence_add_types',
        'expense_campus_ids' => 'expense_campus_ids',
        'inventory_campuses' => 'inventory_campuses',
        'product_request_approval_campuses' => 'product_request_approval_campuses',
        'purchase_campuses' => 'purchase_campuses',
        'pos_campuses' => 'pos_campuses',
        'council_report_colleges' => 'council_report_colleges',
        'council_report_courses' => 'council_report_courses',
        'subject_ids' => 'subject_ids',
        'assignment_subject_ids' => 'assignment_subject_ids',
        'class_ids' => 'class_ids',
        'fee_dues_campus_ids' => 'fee_dues_campus_ids',
        'fee_recovery_class_ids' => 'fee_recovery_class_ids',
    );

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('accesses');
    }

    public function assert_admin($user)
    {
        return $user && isset($user['role']) && $user['role'] === 'Admin';
    }

    public function get_meta()
    {
        $campuses = $this->ci->accesses->getCampuses();
        $departments = $this->ci->accesses->getDepartments();
        $classes = $this->ci->accesses->getClasses();
        $subjects = $this->ci->accesses->getTestEngineSubjects();
        $assignmentSubjects = $this->ci->accesses->getAssignmentsSubjects();

        $this->ci->db->select('*, closing_persons.id as id, campuses.campus_name, users.first_name, users.last_name');
        $this->ci->db->from('closing_persons');
        $this->ci->db->join('campuses', 'campuses.campus_id = closing_persons.campus_id', 'left');
        $this->ci->db->join('users', 'users.user_id = closing_persons.user_id', 'left');
        $this->ci->db->where('closing_persons.active_status', 1);
        $this->ci->db->order_by('closing_persons.id', 'DESC');
        $closingPersons = $this->ci->db->get()->result_array();

        $cashAccounts = $this->ci->db->where('type', 0)->order_by('account_name', 'ASC')->get('accounts')->result_array();
        $bankAccounts = $this->ci->db->where('type', 1)->order_by('account_name', 'ASC')->get('accounts')->result_array();

        $this->ci->db->select('petty_cash_college_wise.id as id, petty_cash_college_wise.assign_to, campuses.campus_name, users.first_name, users.last_name');
        $this->ci->db->from('petty_cash_college_wise');
        $this->ci->db->join('campuses', 'campuses.campus_id = petty_cash_college_wise.campus_id', 'left');
        $this->ci->db->join('users', 'users.user_id = petty_cash_college_wise.assign_to', 'left');
        $this->ci->db->where('petty_cash_college_wise.petty_status', '1');
        $pettyCashUsers = $this->ci->db->get()->result_array();

        $courses = $this->ci->db->order_by('course_name', 'ASC')->get('courses')->result_array();

        return array(
            'campuses' => $campuses,
            'departments' => $departments,
            'classes' => $classes,
            'subjects' => $subjects,
            'assignment_subjects' => $assignmentSubjects,
            'closing_persons' => $closingPersons,
            'cash_accounts' => $cashAccounts,
            'bank_accounts' => $bankAccounts,
            'transfer_accounts' => array_merge($cashAccounts, $bankAccounts),
            'petty_cash_users' => $pettyCashUsers,
            'courses' => $courses,
            'attendance_types' => array(
                array('value' => 'Staff', 'label' => 'Staff'),
                array('value' => 'Student', 'label' => 'Student'),
            ),
        );
    }

    public function get_users($campus_id)
    {
        return $this->ci->db
            ->select('user_id, first_name, last_name')
            ->where(array('campus_id' => (int) $campus_id, 'status' => 1))
            ->order_by('first_name', 'ASC')
            ->get('users')
            ->result_array();
    }

    public function get_designations($department_id)
    {
        return $this->ci->db
            ->where('department_id', (int) $department_id)
            ->order_by('designation_name', 'ASC')
            ->get('designations')
            ->result_array();
    }

    public function load_user($user_id)
    {
        $user_id = (int) $user_id;
        $user = $this->ci->db->get_where('users', array('user_id' => $user_id))->row_array();
        if (!$user) {
            return null;
        }
        $access = $this->ci->db->get_where('access', array('user_id' => $user_id))->row_array();
        $onlineRows = $this->ci->db->get_where('online_application_access', array('user_id' => $user_id))->result_array();
        $onlineCampusIds = array_values(array_unique(array_column($onlineRows, 'campus_id')));

        return array(
            'mode' => 'user',
            'target' => array(
                'user_id' => $user_id,
                'label' => trim($user['first_name'] . ' ' . $user['last_name']),
            ),
            'values' => $this->normalize_values($access ? $access : array(), $onlineCampusIds),
        );
    }

    public function load_designation($designation_id)
    {
        $designation_id = (int) $designation_id;
        $row = $this->ci->db
            ->select('designations.*, departments.department_name')
            ->join('departments', 'departments.department_id = designations.department_id', 'inner')
            ->get_where('designations', array('designations.designation_id' => $designation_id))
            ->row_array();
        if (!$row) {
            return null;
        }
        $access = $this->ci->db->get_where('access_rules', array('designation_id' => $designation_id))->row_array();

        return array(
            'mode' => 'designation',
            'target' => array(
                'designation_id' => $designation_id,
                'label' => $row['department_name'] . ' - ' . $row['designation_name'],
            ),
            'values' => $this->normalize_values($access ? $access : array(), array()),
        );
    }

    public function normalize_values($row, $onlineCampusIds = array())
    {
        $values = is_array($row) ? $row : array();
        foreach (self::$CSV_FIELDS as $field) {
            $formKey = $field;
            if ($field === 'test_engine_subject_ids') {
                $formKey = 'subject_ids';
            }
            if (!empty($values[$field]) && is_string($values[$field])) {
                $values[$formKey] = array_values(array_filter(explode(',', $values[$field]), 'strlen'));
            } elseif ($field === 'test_engine_subject_ids') {
                $values['subject_ids'] = array();
            }
        }
        if (!empty($values['loan_approval'])) {
            $values['loans'] = $values['loan_approval'];
        }
        if (!empty($onlineCampusIds)) {
            $values['online_admission_campus_ids'] = array_map('strval', $onlineCampusIds);
        } else {
            $values['online_admission_campus_ids'] = array();
        }
        return $values;
    }

    public function parse_schema()
    {
        $path = APPPATH . 'views/access/index.php';
        if (!is_readable($path)) {
            return array('sections' => array());
        }
        $lines = file($path);
        $start = false;
        $sections = array();
        $current = 'General';
        $currentFields = array();

        $flush = function () use (&$sections, &$current, &$currentFields) {
            if (!empty($currentFields)) {
                $sections[] = array('title' => $current, 'fields' => $currentFields);
                $currentFields = array();
            }
        };

        foreach ($lines as $line) {
            if (strpos($line, '/access/add') !== false) {
                $start = true;
                continue;
            }
            if (!$start) {
                continue;
            }
            if (strpos($line, 'form-actions') !== false) {
                break;
            }
            if (preg_match('/control-label"><strong>([^<]+)<\/strong>/', $line, $m)) {
                $flush();
                $current = html_entity_decode(trim(strip_tags($m[1])), ENT_QUOTES, 'UTF-8');
                continue;
            }
            if (preg_match('/control-label">\s*([^<]+?)\s*<span class="required">/', $line, $m)) {
                // nested multiselect label
                $label = trim(strip_tags($m[1]));
            } elseif (preg_match('/control-label">([^<]+)</', $line, $m) && strpos($line, 'col-md-3') !== false) {
                $label = trim(strip_tags($m[1]));
            } else {
                $label = null;
            }
            if (preg_match('/name="([a-z0-9_]+)" value="1"[^>]*\/>\s*([^<]+)</i', $line, $m)) {
                $currentFields[] = array(
                    'name' => $m[1],
                    'label' => trim(strip_tags($m[2])),
                    'type' => 'checkbox',
                );
                continue;
            }
            if (preg_match('/name="([a-z0-9_]+)\[\]" multiple/', $line, $m)) {
                $name = $m[1];
                $currentFields[] = array(
                    'name' => $name,
                    'label' => $label ? $label : $this->humanize_field($name),
                    'type' => 'multiselect',
                    'optionsKey' => $this->options_key_for($name),
                );
            }
        }
        $flush();

        return array('sections' => $sections);
    }

    private function humanize_field($name)
    {
        return ucwords(str_replace('_', ' ', $name));
    }

    private function options_key_for($name)
    {
        $map = array(
            'campus_ids' => 'campuses',
            'online_admission_campus_ids' => 'campuses',
            'campus_closing_ids' => 'closing_persons',
            'allowed_cash_account_ids' => 'cash_accounts',
            'allowed_bank_account_ids' => 'bank_accounts',
            'funds_transfer_account_ids' => 'transfer_accounts',
            'account_details_pettycash_ids' => 'petty_cash_users',
            'petty_cash_users' => 'petty_cash_users',
            'attendence_add_types' => 'attendance_types',
            'expense_campus_ids' => 'campuses',
            'inventory_campuses' => 'campuses',
            'product_request_approval_campuses' => 'campuses',
            'purchase_campuses' => 'campuses',
            'pos_campuses' => 'campuses',
            'council_report_colleges' => 'campuses',
            'council_report_courses' => 'courses',
            'subject_ids' => 'subjects',
            'assignment_subject_ids' => 'assignment_subjects',
            'class_ids' => 'classes',
            'fee_dues_campus_ids' => 'campuses',
            'fee_recovery_class_ids' => 'classes',
        );
        return isset($map[$name]) ? $map[$name] : 'campuses';
    }

    public function save_from_body($body)
    {
        if (!is_array($body)) {
            $body = array();
        }

        // Build a complete payload from schema so unchecked boxes / empty multiselects
        // are sent explicitly (legacy HTML form behaviour).
        $prepared = array();
        $schema = $this->parse_schema();
        foreach ($schema['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $name = $field['name'];
                if ($field['type'] === 'checkbox') {
                    $prepared[$name] = !empty($body[$name]) ? 1 : 0;
                } elseif ($field['type'] === 'multiselect') {
                    $prepared[$name] = (isset($body[$name]) && is_array($body[$name]))
                        ? array_values($body[$name])
                        : array();
                }
            }
        }

        if (!empty($body['user_id'])) {
            $prepared['user_id'] = (int) $body['user_id'];
        }
        if (!empty($body['designation_id'])) {
            $prepared['designation_id'] = (int) $body['designation_id'];
        }

        if (empty($prepared['user_id']) && empty($prepared['designation_id'])) {
            return array('success' => false, 'message' => 'user_id or designation_id required');
        }

        $_POST = array();
        foreach ($prepared as $key => $value) {
            if (is_array($value)) {
                $_POST[$key] = $value;
            } elseif ($value === 0 || $value === '0') {
                $_POST[$key] = 0;
            } elseif ($value !== null && $value !== '') {
                $_POST[$key] = $value;
            }
        }

        $check = $this->ci->accesses->check();
        if (count($check) > 0) {
            $this->ci->accesses->updateAccess();
        } else {
            $this->ci->accesses->addAccess();
        }

        $user_id = isset($prepared['user_id']) ? (int) $prepared['user_id'] : 0;
        $onlineIds = isset($prepared['online_admission_campus_ids']) && is_array($prepared['online_admission_campus_ids'])
            ? $prepared['online_admission_campus_ids']
            : array();

        if ($user_id) {
            $this->ci->db->where('user_id', $user_id)->delete('online_application_access');
            foreach ($onlineIds as $campus_id) {
                if ($campus_id === '' || $campus_id === null) {
                    continue;
                }
                $this->ci->db->insert('online_application_access', array(
                    'user_id' => $user_id,
                    'campus_id' => $campus_id,
                    'city' => '',
                    'all_cities' => 1,
                ));
            }
        }

        return array('success' => true, 'message' => 'Access has been granted successfully');
    }
}
