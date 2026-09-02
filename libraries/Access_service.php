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
        'cities', 'other_cities_access', 'online_admission_campus_ids',
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

        return array(
            'mode' => 'user',
            'target' => array(
                'user_id' => $user_id,
                'label' => trim($user['first_name'] . ' ' . $user['last_name']),
            ),
            'values' => $this->normalize_values($access ? $access : array()),
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
            } elseif ($field === 'online_admission_campus_ids') {
                $values['online_admission_campus_ids'] = array();
            }
        }
        if (!empty($values['loan_approval'])) {
            $values['loans'] = $values['loan_approval'];
        }
        // Legacy fallback: online_application_access table before column migration
        if (empty($values['online_admission_campus_ids']) && !empty($onlineCampusIds)) {
            $values['online_admission_campus_ids'] = array_map('strval', $onlineCampusIds);
        } elseif (empty($values['online_admission_campus_ids'])) {
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
            if (stripos($line, 'type="checkbox"') !== false && preg_match('/name="([a-z0-9_]+)"/i', $line, $m)) {
                $labelText = $this->humanize_field($m[1]);
                $closePos = strrpos($line, '/>');
                if ($closePos !== false) {
                    $tail = substr($line, $closePos + 2);
                    if (preg_match('/^\s*(.*?)\s*<\/label>/s', $tail, $lm)) {
                        $parsed = trim(strip_tags($lm[1]));
                        if ($parsed !== '') {
                            $labelText = $parsed;
                        }
                    }
                }
                $currentFields[] = array(
                    'name' => $m[1],
                    'label' => $labelText,
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

    /**
     * Reverse report: which users / designations have each permission.
     * GET params: q (search), section (title filter), field (selected key for assignee lists).
     */
    public function who_has_report($q = '', $section_filter = '', $field = '')
    {
        $schema = $this->parse_schema();
        $q = strtolower(trim((string) $q));
        $section_filter = trim((string) $section_filter);
        $field = trim((string) $field);

        $db_col = function ($form_name) {
            if ($form_name === 'subject_ids') {
                return 'test_engine_subject_ids';
            }
            if ($form_name === 'loans') {
                return 'loan_approval';
            }
            return $form_name;
        };

        $is_granted = function ($row, $form_name, $type) use ($db_col) {
            $col = $db_col($form_name);
            $val = null;
            if (array_key_exists($col, $row)) {
                $val = $row[$col];
            } elseif (array_key_exists($form_name, $row)) {
                $val = $row[$form_name];
            }
            if ($type === 'multiselect') {
                if ($val === null || $val === '' || $val === '0') {
                    return false;
                }
                if (is_array($val)) {
                    return count(array_filter($val, 'strlen')) > 0;
                }
                return trim((string) $val) !== '';
            }
            return $val === 1 || $val === '1' || $val === true;
        };

        $scope_summary = function ($row, $form_name, $type) use ($db_col) {
            if ($type !== 'multiselect') {
                return '';
            }
            $col = $db_col($form_name);
            $raw = '';
            if (array_key_exists($col, $row) && $row[$col] !== null && $row[$col] !== '') {
                $raw = is_array($row[$col]) ? implode(',', $row[$col]) : (string) $row[$col];
            } elseif (array_key_exists($form_name, $row) && $row[$form_name] !== null && $row[$form_name] !== '') {
                $raw = is_array($row[$form_name]) ? implode(',', $row[$form_name]) : (string) $row[$form_name];
            }
            $ids = array_values(array_filter(array_map('trim', explode(',', $raw)), 'strlen'));
            if (!count($ids)) {
                return '';
            }
            $shown = array_slice($ids, 0, 6);
            $extra = count($ids) - count($shown);
            $text = implode(', ', $shown);
            if ($extra > 0) {
                $text .= ' +' . $extra . ' more';
            }
            return $text;
        };

        $access_rows = array();
        if ($this->ci->db->table_exists('access')) {
            $this->ci->db->select(
                'access.*, users.user_id AS _uid, users.first_name, users.last_name, campuses.campus_name',
                false
            );
            $this->ci->db->from('access');
            $this->ci->db->join('users', 'users.user_id = access.user_id', 'inner');
            $this->ci->db->join('campuses', 'campuses.campus_id = users.campus_id', 'left');
            $this->ci->db->where('users.status', '1');
            $this->ci->db->order_by('users.first_name', 'ASC');
            $this->ci->db->order_by('users.last_name', 'ASC');
            $access_rows = $this->ci->db->get()->result_array();
        }

        $rule_rows = array();
        if ($this->ci->db->table_exists('access_rules')) {
            $this->ci->db->select(
                'access_rules.*, designations.designation_id AS _did, designations.designation_name, departments.department_name',
                false
            );
            $this->ci->db->from('access_rules');
            $this->ci->db->join('designations', 'designations.designation_id = access_rules.designation_id', 'inner');
            $this->ci->db->join('departments', 'departments.department_id = designations.department_id', 'left');
            $this->ci->db->order_by('departments.department_name', 'ASC');
            $this->ci->db->order_by('designations.designation_name', 'ASC');
            $rule_rows = $this->ci->db->get()->result_array();
        }

        $sections_out = array();
        $selected_meta = null;
        $selected_users = array();
        $selected_designations = array();

        foreach ($schema['sections'] as $section) {
            $title = isset($section['title']) ? $section['title'] : '';
            if ($section_filter !== '' && strcasecmp($title, $section_filter) !== 0) {
                continue;
            }
            $fields_out = array();
            foreach ($section['fields'] as $f) {
                $name = isset($f['name']) ? $f['name'] : '';
                $label = isset($f['label']) ? $f['label'] : $name;
                $type = isset($f['type']) ? $f['type'] : 'checkbox';
                if ($name === '') {
                    continue;
                }
                if ($q !== '') {
                    $hay = strtolower($label . ' ' . $name . ' ' . $title);
                    if (strpos($hay, $q) === false) {
                        continue;
                    }
                }

                $user_count = 0;
                $desig_count = 0;
                foreach ($access_rows as $row) {
                    if ($is_granted($row, $name, $type)) {
                        $user_count++;
                    }
                }
                foreach ($rule_rows as $row) {
                    if ($is_granted($row, $name, $type)) {
                        $desig_count++;
                    }
                }

                if ($user_count === 0 && $desig_count === 0) {
                    continue;
                }

                $fields_out[] = array(
                    'name' => $name,
                    'label' => $label,
                    'type' => $type,
                    'optionsKey' => isset($f['optionsKey']) ? $f['optionsKey'] : null,
                    'user_count' => $user_count,
                    'designation_count' => $desig_count,
                );

                if ($field !== '' && $field === $name) {
                    $selected_meta = array(
                        'name' => $name,
                        'label' => $label,
                        'type' => $type,
                        'section' => $title,
                        'user_count' => $user_count,
                        'designation_count' => $desig_count,
                    );
                    foreach ($access_rows as $row) {
                        if (!$is_granted($row, $name, $type)) {
                            continue;
                        }
                        $selected_users[] = array(
                            'user_id' => (int) (isset($row['_uid']) ? $row['_uid'] : (isset($row['user_id']) ? $row['user_id'] : 0)),
                            'name' => trim(
                                (isset($row['first_name']) ? $row['first_name'] : '') . ' ' .
                                (isset($row['last_name']) ? $row['last_name'] : '')
                            ),
                            'campus_name' => isset($row['campus_name']) ? $row['campus_name'] : '',
                            'scope_summary' => $scope_summary($row, $name, $type),
                        );
                    }
                    foreach ($rule_rows as $row) {
                        if (!$is_granted($row, $name, $type)) {
                            continue;
                        }
                        $dept = isset($row['department_name']) ? $row['department_name'] : '';
                        $dname = isset($row['designation_name']) ? $row['designation_name'] : '';
                        $selected_designations[] = array(
                            'designation_id' => (int) (isset($row['_did']) ? $row['_did'] : (isset($row['designation_id']) ? $row['designation_id'] : 0)),
                            'label' => trim($dept . ($dept !== '' && $dname !== '' ? ' · ' : '') . $dname),
                            'scope_summary' => $scope_summary($row, $name, $type),
                        );
                    }
                }
            }
            if (count($fields_out)) {
                $sections_out[] = array('title' => $title, 'fields' => $fields_out);
            }
        }

        return array(
            'sections' => $sections_out,
            'field' => $selected_meta,
            'users' => $selected_users,
            'designations' => $selected_designations,
        );
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

        return array('success' => true, 'message' => 'Access has been granted successfully');
    }

    /**
     * Human-readable granted permissions for a designation or user access row.
     */
    public function summarize_granted_access($values)
    {
        if (!is_array($values)) {
            $values = array();
        }

        $schema = $this->parse_schema();
        $meta = $this->get_meta();
        $sections = array();

        foreach ($schema['sections'] as $section) {
            $items = array();
            foreach ($section['fields'] as $field) {
                $name = $field['name'];
                if ($field['type'] === 'checkbox') {
                    if (!empty($values[$name])) {
                        $items[] = array(
                            'label' => $field['label'],
                            'values' => array(),
                        );
                    }
                    continue;
                }

                if ($field['type'] !== 'multiselect') {
                    continue;
                }

                $selected = isset($values[$name]) ? $values[$name] : array();
                if (!is_array($selected)) {
                    $selected = array_values(array_filter(explode(',', (string) $selected), 'strlen'));
                }
                if (empty($selected)) {
                    continue;
                }

                $optionsKey = isset($field['optionsKey']) ? $field['optionsKey'] : 'campuses';
                $labels = $this->resolve_option_labels($optionsKey, $selected, $meta);
                $items[] = array(
                    'label' => $field['label'],
                    'values' => $labels,
                );
            }

            if (!empty($items)) {
                $sections[] = array(
                    'title' => $section['title'],
                    'items' => $items,
                );
            }
        }

        return $sections;
    }

    private function resolve_option_labels($optionsKey, $selected, $meta)
    {
        $options = isset($meta[$optionsKey]) && is_array($meta[$optionsKey]) ? $meta[$optionsKey] : array();
        $lookup = array();
        foreach ($options as $row) {
            if (!is_array($row)) {
                continue;
            }
            $value = $this->option_value($row, $optionsKey);
            $label = $this->option_label($row, $optionsKey);
            if ($value !== '') {
                $lookup[$value] = $label;
            }
        }

        $labels = array();
        foreach ($selected as $id) {
            $key = (string) $id;
            $labels[] = isset($lookup[$key]) ? $lookup[$key] : $key;
        }

        return array_values(array_unique($labels));
    }

    private function option_label($row, $optionsKey)
    {
        switch ($optionsKey) {
            case 'campuses':
                return (string) (isset($row['campus_name']) ? $row['campus_name'] : (isset($row['campus_id']) ? $row['campus_id'] : ''));
            case 'classes':
                return (string) (isset($row['name']) ? $row['name'] : (isset($row['class_id']) ? $row['class_id'] : ''));
            case 'courses':
                return (string) (isset($row['course_name']) ? $row['course_name'] : (isset($row['course_id']) ? $row['course_id'] : ''));
            case 'subjects':
            case 'assignment_subjects':
                $subject = isset($row['subject_name']) ? $row['subject_name'] : '';
                $course = isset($row['course_name']) ? $row['course_name'] : '';
                return trim($subject . ($course !== '' ? ' (' . $course . ')' : ''));
            case 'closing_persons':
            case 'petty_cash_users':
                $campus = isset($row['campus_name']) ? $row['campus_name'] : '';
                $user = trim((isset($row['first_name']) ? $row['first_name'] : '') . ' ' . (isset($row['last_name']) ? $row['last_name'] : ''));
                return trim($campus . ($user !== '' ? ' - ' . $user : ''));
            case 'cash_accounts':
            case 'bank_accounts':
            case 'transfer_accounts':
                return (string) (isset($row['account_name']) ? $row['account_name'] : (isset($row['account_id']) ? $row['account_id'] : ''));
            case 'attendance_types':
                return (string) (isset($row['label']) ? $row['label'] : (isset($row['value']) ? $row['value'] : ''));
            default:
                if (isset($row['label'])) {
                    return (string) $row['label'];
                }
                if (isset($row['name'])) {
                    return (string) $row['name'];
                }
                return (string) (isset($row['id']) ? $row['id'] : '');
        }
    }

    private function option_value($row, $optionsKey)
    {
        switch ($optionsKey) {
            case 'campuses':
                return (string) (isset($row['campus_id']) ? $row['campus_id'] : '');
            case 'classes':
                return (string) (isset($row['class_id']) ? $row['class_id'] : '');
            case 'courses':
                return (string) (isset($row['course_id']) ? $row['course_id'] : '');
            case 'subjects':
            case 'assignment_subjects':
                return (string) (isset($row['course_subject_id']) ? $row['course_subject_id'] : '');
            case 'closing_persons':
            case 'petty_cash_users':
                return (string) (isset($row['id']) ? $row['id'] : '');
            case 'cash_accounts':
            case 'bank_accounts':
            case 'transfer_accounts':
                return (string) (isset($row['account_id']) ? $row['account_id'] : '');
            case 'attendance_types':
                return (string) (isset($row['value']) ? $row['value'] : '');
            default:
                return (string) (isset($row['id']) ? $row['id'] : '');
        }
    }
}
