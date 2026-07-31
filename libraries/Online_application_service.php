<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Online applications business logic for Onlineapplicationsapi JSON endpoints.
 */
class Online_application_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('dashboards');
        $this->ensure_dynamic_form_tables();
    }

    public function ensure_dynamic_form_tables()
    {
        $this->ci->db->query("CREATE TABLE IF NOT EXISTS dynamic_forms (
			id INT NOT NULL AUTO_INCREMENT,
			title VARCHAR(255) NOT NULL,
			slug VARCHAR(255) NOT NULL,
			description TEXT NULL,
			status TINYINT(1) NOT NULL DEFAULT 1,
			created_by INT NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NULL,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->ci->db->query("CREATE TABLE IF NOT EXISTS dynamic_form_fields (
			id INT NOT NULL AUTO_INCREMENT,
			form_id INT NOT NULL,
			label VARCHAR(255) NOT NULL,
			field_name VARCHAR(255) NOT NULL,
			field_type VARCHAR(50) NOT NULL,
			options TEXT NULL,
			is_required TINYINT(1) NOT NULL DEFAULT 0,
			row_index INT NOT NULL DEFAULT 0,
			column_width INT NOT NULL DEFAULT 12,
			sort_order INT NOT NULL DEFAULT 0,
			PRIMARY KEY (id),
			KEY form_id (form_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        if (!$this->ci->db->field_exists('row_index', 'dynamic_form_fields')) {
            $this->ci->db->query("ALTER TABLE dynamic_form_fields ADD row_index INT NOT NULL DEFAULT 0 AFTER is_required");
        }
        if (!$this->ci->db->field_exists('column_width', 'dynamic_form_fields')) {
            $this->ci->db->query("ALTER TABLE dynamic_form_fields ADD column_width INT NOT NULL DEFAULT 12 AFTER row_index");
        }

        $this->ci->db->query("CREATE TABLE IF NOT EXISTS dynamic_form_submissions (
			id INT NOT NULL AUTO_INCREMENT,
			form_id INT NOT NULL,
			status TINYINT(1) NOT NULL DEFAULT 0,
			ip_address VARCHAR(64) NULL,
			user_agent TEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY form_id (form_id),
			KEY status (status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->ci->db->query("CREATE TABLE IF NOT EXISTS dynamic_form_submission_values (
			id INT NOT NULL AUTO_INCREMENT,
			submission_id INT NOT NULL,
			field_id INT NOT NULL,
			field_label VARCHAR(255) NOT NULL,
			field_name VARCHAR(255) NOT NULL,
			field_type VARCHAR(50) NOT NULL,
			value TEXT NULL,
			PRIMARY KEY (id),
			KEY submission_id (submission_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }

    public function actor_name($user)
    {
        $name = trim($user['first_name'] . ' ' . $user['last_name']);
        return $name !== '' ? $name : 'POS';
    }

    public function bind_session($user)
    {
        $this->ci->session->set_userdata('user_id', $user['user_id']);
        $this->ci->session->set_userdata('role', $user['role']);
        $this->ci->session->set_userdata('name', $this->actor_name($user));
    }

    public function permissions($user)
    {
        $is_admin = $user['role'] === 'Admin';
        $access = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        if (!$access) {
            $access = array();
        }
        return array(
            'is_admin' => $is_admin,
            'has_access' => $is_admin || !empty($access['online_application_access']),
            'new_admissions' => $is_admin || !empty($access['online_application_new_admissions']),
            'checked_admissions' => $is_admin || !empty($access['online_application_checked_admissions']),
            'all' => $is_admin || !empty($access['online_application_all']),
            'facebook_leads' => $is_admin || !empty($access['facebook_leads']),
        );
    }

    public function counts()
    {
        return array(
            'new' => (int) newApplicationsCount(),
            'pending' => (int) pendingApplicationsCount(),
        );
    }

    public function campuses_for_user($user)
    {
        if ($user['role'] === 'Admin') {
            return $this->ci->db->get_where('campuses', array('status' => 1))->result_array();
        }
        $ids = getUserOnlineApplicationCampusIds();
        if (empty($ids)) {
            return array();
        }
        $this->ci->db->where_in('campus_id', $ids);
        $this->ci->db->where('status', 1);
        return $this->ci->db->get('campuses')->result_array();
    }

	private function get_dynamic_form_submissions($status = 0, $date_from = null, $date_to = null)
	{
		$this->ci->db->select('dynamic_form_submissions.*, dynamic_forms.title as form_title, dynamic_forms.slug');
		$this->ci->db->from('dynamic_form_submissions');
		$this->ci->db->join('dynamic_forms', 'dynamic_forms.id = dynamic_form_submissions.form_id', 'inner');
		if ($status !== null) {
			$this->ci->db->where('dynamic_form_submissions.status', $status);
		}
		if ($date_from) {
			$this->ci->db->where('DATE(dynamic_form_submissions.created_at) >=', $date_from);
		}
		if ($date_to) {
			$this->ci->db->where('DATE(dynamic_form_submissions.created_at) <=', $date_to);
		}
		$this->ci->db->order_by('dynamic_form_submissions.id', 'DESC');
		return $this->ci->db->get()->result_array();
	}

    private function submission_values($submission_id)
    {
        return $this->ci->db
            ->where('submission_id', $submission_id)
            ->order_by('id', 'ASC')
            ->get('dynamic_form_submission_values')
            ->result_array();
    }

    public function enrich_apply_now_row($row)
    {
        $admission = $row;
        $msg = '';
        $cnic_check = array();
        $mobile_check = array();

        if (trim((string) $admission['cnic']) !== '') {
            $cnic_check = $this->find_students_by_cnic($admission['cnic']);
        } else {
            $mobile = trim($admission['mobile']);
            if ($mobile != '' && preg_match('/^[0-9+\-\s]+$/', $mobile)) {
                $this->ci->db->group_start();
                $this->ci->db->where('mobile', $mobile);
                $this->ci->db->or_where('emergency_no', $mobile);
                $this->ci->db->group_end();
                $mobile_check = $this->ci->db->get('students')->result_array();
            }
        }

        $mobile = trim($admission['mobile']);
        $check_double_entry = array();
        if ($mobile != '' && preg_match('/^[0-9+\-\s]+$/', $mobile)) {
            $this->ci->db->group_start();
            $this->ci->db->where('mobile', $mobile);
            $this->ci->db->or_where('emergency_no', $mobile);
            $this->ci->db->group_end();
            $check_double_entry = $this->ci->db->get('apply_now')->result_array();
        }

        if (count($check_double_entry) > 1) {
            $this->ci->db->where('apply_now_id !=', $admission['apply_now_id']);
            $this->ci->db->where("(mobile='" . $this->ci->db->escape_str($admission['mobile']) . "' OR emergency_no='" . $this->ci->db->escape_str($admission['mobile']) . "')", null, false);
            $entries = $this->ci->db->get('apply_now')->result_array();
            foreach ($entries as $entry) {
                $campus = $this->ci->db->get_where('campuses', array(
                    'website' => str_replace('/', '', str_replace('https://www.', '', $entry['website'])),
                ))->row();
                $campus_name = $campus ? $campus->campus_name : $entry['website'];
                $msg .= 'Student Already apply in ' . $campus_name . ' on ' . date('Y-m-d', strtotime($entry['date'])) . "\n";
            }
        }

        $about_student = array();
        foreach ($cnic_check as $student) {
            $about_student[] = array(
                'roll_no' => $student['roll_no'],
                'status' => $student['status'] == 1 ? 'Active' : 'Deactive',
                'registration_date' => $student['registration_date'],
            );
        }
        foreach ($mobile_check as $student) {
            $about_student[] = array(
                'roll_no' => $student['roll_no'],
                'status' => $student['status'] == 1 ? 'Active' : 'Deactive',
                'registration_date' => $student['registration_date'],
            );
        }

        $comments = $this->ci->db
            ->order_by('online_application_comment_id', 'ASC')
            ->get_where('online_application_comments', array('apply_now_id' => $admission['apply_now_id']))
            ->result_array();

        $row['system_comment'] = $msg;
        $row['about_student'] = $about_student;
        $row['is_existing_student'] = !empty($about_student);
        $row['comments'] = $comments;
        $row['source'] = 'website';
        return $row;
    }

    public function enrich_mobile_row($row)
    {
        $qual = json_decode($row['qualification'], true);
        $education = '';
        if (@$qual['Qualification']) {
            $parts = array();
            if (!empty($qual['Qualification']['Matriculation'])) {
                $parts[] = 'Matric';
            }
            if (!empty($qual['Qualification']['Intermediate'])) {
                $parts[] = 'Intermediate';
            }
            $education = implode(', ', $parts);
        }
        $row['education'] = $education;
        $row['name'] = trim($row['first_name'] . ' ' . $row['last_name']);
        $row['website'] = 'Mobile App';
        $row['date'] = $row['entry_date'];
        $row['source'] = 'mobile';
        $row['apply_now_id'] = null;
        $row['comments'] = array();
        $row['system_comment'] = '';
        $row['about_student'] = array();
        $row['is_existing_student'] = false;
        return $row;
    }

    public function new_applications($campus_id = null)
    {
        $website = $this->ci->dashboards->getNewAdmisssions($campus_id);
        $mobile = $this->ci->dashboards->getNewMobileAdmisssions($campus_id);
        $dynamic = $this->get_dynamic_form_submissions(0);

        $rows = array();
        foreach ($website as $r) {
            $rows[] = $this->enrich_apply_now_row($r);
        }
        foreach ($mobile as $r) {
            $rows[] = $this->enrich_mobile_row($r);
        }

        foreach ($dynamic as $sub) {
            $sub['source'] = 'dynamic_form';
            $sub['values'] = $this->submission_values($sub['id']);
            $rows[] = $sub;
        }

        return $rows;
    }

    public function pending_applications($campus_id = null)
    {
        $today = array();
        foreach ($this->ci->dashboards->getPendingAdmisssions($campus_id, 'today') as $r) {
            $today[] = $this->enrich_apply_now_row($r);
        }
        $future = array();
        foreach ($this->ci->dashboards->getPendingAdmisssions($campus_id, 'future') as $r) {
            $future[] = $this->enrich_apply_now_row($r);
        }
        return array('today' => $today, 'future' => $future);
    }

    public function checked_applications()
    {
        $rows = array();
        foreach ($this->ci->dashboards->getNewClearAdmisssions() as $r) {
            $rows[] = $this->enrich_apply_now_row($r);
        }
        return $rows;
    }

    public function all_applications($filters = array())
    {
        $campus_id = !empty($filters['campus_id']) ? $filters['campus_id'] : null;
        $date_from = !empty($filters['date_from']) ? $filters['date_from'] : null;
        $date_to = !empty($filters['date_to']) ? $filters['date_to'] : null;

        $rows = array();

        foreach ($this->ci->dashboards->getAllApplicationsReport($campus_id, $date_from, $date_to) as $r) {
            $enriched = $this->enrich_apply_now_row($r);
            if (!$this->can_view_confirmed_row($enriched)) {
                continue;
            }
            $enriched['workflow_status'] = $this->apply_now_workflow_status($r);
            $rows[] = $enriched;
        }

        foreach ($this->ci->dashboards->getAllMobileAdmissions($campus_id, $date_from, $date_to) as $r) {
            $enriched = $this->enrich_mobile_row($r);
            $enriched['workflow_status'] = 'Mobile App';
            $rows[] = $enriched;
        }

        foreach ($this->get_dynamic_form_submissions(null, $date_from, $date_to) as $sub) {
            $sub['source'] = 'dynamic_form';
            $sub['values'] = $this->submission_values($sub['id']);
            $sub['workflow_status'] = !empty($sub['status']) ? 'Form Checked' : 'Dynamic Form';
            $rows[] = $sub;
        }

        usort($rows, function ($a, $b) {
            $da = strtotime(isset($a['date']) ? $a['date'] : (isset($a['created_at']) ? $a['created_at'] : '0'));
            $db = strtotime(isset($b['date']) ? $b['date'] : (isset($b['created_at']) ? $b['created_at'] : '0'));
            if ($da === $db) {
                return 0;
            }
            return ($da > $db) ? -1 : 1;
        });

        return $rows;
    }

    private function apply_now_workflow_status($row)
    {
        if ((int) $row['status'] === 1 && (int) $row['clear_by_admin'] === 1) {
            return 'Admin Cleared';
        }
        if ((int) $row['status'] === 1) {
            return 'Checked';
        }
        if ((int) $row['pending_status'] === 1) {
            return 'Pending';
        }
        return 'New';
    }

    public function confirmed_admissions()
    {
        $seen = array();
        $rows = array();

        foreach ($this->fetch_confirmed_apply_now_rows() as $r) {
            $id = (int) $r['apply_now_id'];
            if (isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $enriched = $this->enrich_apply_now_row($r);
            if (empty($enriched['about_student'])) {
                continue;
            }
            if (!$this->can_view_confirmed_row($enriched)) {
                continue;
            }
            $rows[] = $enriched;
        }

        return $rows;
    }

    private function normalized_cnic_expr($column)
    {
        return "REPLACE(REPLACE(REPLACE(TRIM({$column}), '-', ''), ' ', ''), '.', '')";
    }

    private function find_students_by_cnic($cnic)
    {
        $norm = $this->normalize_cnic_value($cnic);
        if ($norm === '') {
            return array();
        }
        $expr = $this->normalized_cnic_expr('cnic');
        return $this->ci->db
            ->where("({$expr}) = " . $this->ci->db->escape($norm), null, false)
            ->get('students')
            ->result_array();
    }

    private function normalize_cnic_value($cnic)
    {
        return preg_replace('/[\s\-\.]/', '', trim((string) $cnic));
    }

    private function fetch_confirmed_apply_now_rows()
    {
        $normApply = $this->normalized_cnic_expr('apply_now.cnic');
        $normStudent = $this->normalized_cnic_expr('students.cnic');

        $this->ci->db->select('apply_now.*');
        $this->ci->db->from('apply_now');
        $this->ci->db->join(
            'students',
            "({$normApply}) != '' AND ({$normApply}) = ({$normStudent})",
            'inner',
            false
        );
        $this->ci->db->group_by('apply_now.apply_now_id');
        $byCnic = $this->ci->db->get()->result_array();

        $this->ci->db->select('apply_now.*');
        $this->ci->db->from('apply_now');
        $this->ci->db->join(
            'students',
            "((apply_now.cnic IS NULL OR TRIM(apply_now.cnic) = '') AND TRIM(apply_now.mobile) != '' AND (TRIM(students.mobile) = TRIM(apply_now.mobile) OR TRIM(students.emergency_no) = TRIM(apply_now.mobile)))",
            'inner',
            false
        );
        $this->ci->db->group_by('apply_now.apply_now_id');
        $byMobile = $this->ci->db->get()->result_array();

        return array_merge($byCnic, $byMobile);
    }

    private function can_view_confirmed_row($row)
    {
        if ($this->ci->session->userdata('role') === 'Admin') {
            return true;
        }

        $website = isset($row['website']) ? (string) $row['website'] : '';
        $city = isset($row['city']) ? (string) $row['city'] : '';
        $user_id = (int) $this->ci->session->userdata('user_id');

        $campus = $this->ci->db->get_where('campuses', array(
            'website' => str_replace('/', '', str_replace('https://www.', '', $website)),
        ))->row_array();
        if (!$campus) {
            return false;
        }

        $campus_id = (int) $campus['campus_id'];
        $direct = $this->ci->db->get_where('online_application_access', array(
            'campus_id' => $campus_id,
            'city' => $city,
            'user_id' => $user_id,
        ))->result_array();
        if (!empty($direct)) {
            return true;
        }

        $cityRows = $this->ci->db->get_where('online_application_access', array(
            'campus_id' => $campus_id,
            'city' => $city,
        ))->result_array();
        if (!empty($cityRows)) {
            return false;
        }

        $allCities = $this->ci->db->get_where('online_application_access', array(
            'campus_id' => $campus_id,
            'all_cities' => 1,
            'user_id' => $user_id,
        ))->result_array();

        return !empty($allCities);
    }

    public function add_comment($body, $user)
    {
        $interest_type = isset($body['interest_type']) ? $body['interest_type'] : '';
        $date = isset($body['date']) ? $body['date'] : 0;
        $next_date_for_call = isset($body['next_date_for_call']) ? $body['next_date_for_call'] : '';
        $comment = isset($body['comment']) ? $body['comment'] : '';
        $apply_now_id = isset($body['apply_now_id']) ? (int) $body['apply_now_id'] : 0;
        $add_by = $this->actor_name($user);

        if (!$apply_now_id || $interest_type === '') {
            return array('success' => false, 'message' => 'apply_now_id and interest_type required');
        }

        $this->ci->db->set('interest_type', $interest_type);
        if ($date == 1 || $date === '1' || $date === true) {
            $this->ci->db->set('next_date_for_call', $next_date_for_call);
        } else {
            $this->ci->db->set('next_date_for_call', '0000-00-00');
        }
        $this->ci->db->set('comment', $comment);
        $this->ci->db->set('add_by', $add_by);
        $this->ci->db->set('apply_now_id', $apply_now_id);
        $this->ci->db->set('add_date_time', date('Y-m-d H:i:s'));
        $this->ci->db->insert('online_application_comments');

        if ($interest_type == 'Not Interested') {
            $this->ci->db->set('pending_status', '0');
            $this->ci->db->set('status', '1');
        } else {
            $this->ci->db->set('pending_status', '1');
            $this->ci->db->set('status', '0');
        }
        $this->ci->db->set('last_edit', $add_by);
        $this->ci->db->where('apply_now_id', $apply_now_id);
        $this->ci->db->update('apply_now');

        return array('success' => true, 'message' => 'Comment added');
    }

    public function clear_pending($apply_now_id)
    {
        $apply_now_id = (int) $apply_now_id;
        if (!$apply_now_id) {
            return array('success' => false, 'message' => 'apply_now_id required');
        }
        $this->ci->db->set('pending_status', 0);
        $this->ci->db->set('status', 1);
        $this->ci->db->where('apply_now_id', $apply_now_id);
        $this->ci->db->update('apply_now');
        return array('success' => true, 'message' => 'Application cleared');
    }

    public function clear_checked($apply_now_id)
    {
        $apply_now_id = (int) $apply_now_id;
        if (!$apply_now_id) {
            return array('success' => false, 'message' => 'apply_now_id required');
        }
        $this->ci->db->set('clear_by_admin', 1);
        $this->ci->db->where('apply_now_id', $apply_now_id);
        $this->ci->db->update('apply_now');
        return array('success' => true, 'message' => 'Application admin-cleared');
    }

    public function mark_dynamic_checked($submission_id)
    {
        $submission_id = (int) $submission_id;
        if (!$submission_id) {
            return array('success' => false, 'message' => 'submission_id required');
        }
        $this->ci->db->where('id', $submission_id)->update('dynamic_form_submissions', array('status' => 1));
        return array('success' => true, 'message' => 'Submission marked checked');
    }

    public function dynamic_forms_list()
    {
        return $this->ci->db->order_by('id', 'DESC')->get('dynamic_forms')->result_array();
    }

    public function dynamic_form_get($id)
    {
        $id = (int) $id;
        $form = $this->ci->db->get_where('dynamic_forms', array('id' => $id))->row_array();
        if (!$form) {
            return null;
        }
        $fields = $this->ci->db
            ->where('form_id', $id)
            ->order_by('row_index', 'ASC')
            ->order_by('sort_order', 'ASC')
            ->get('dynamic_form_fields')
            ->result_array();
        return array('form' => $form, 'fields' => $fields);
    }

    private function make_slug($text)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
        return $slug != '' ? $slug : 'form';
    }

    private function unique_form_slug($title, $ignore_id = 0)
    {
        $base = $this->make_slug($title);
        $slug = $base;
        $i = 2;
        while (true) {
            $this->ci->db->where('slug', $slug);
            if ($ignore_id > 0) {
                $this->ci->db->where('id !=', $ignore_id);
            }
            $exists = $this->ci->db->get('dynamic_forms')->row_array();
            if (!$exists) {
                return $slug;
            }
            $slug = $base . '-' . $i;
            $i++;
        }
    }

    public function save_dynamic_form($body, $user)
    {
        $form_id = isset($body['form_id']) ? (int) $body['form_id'] : 0;
        $title = trim(isset($body['title']) ? $body['title'] : '');
        $description = isset($body['description']) ? $body['description'] : '';
        $status = !empty($body['status']) ? 1 : 0;
        $fields = isset($body['fields']) && is_array($body['fields']) ? $body['fields'] : array();

        if ($title === '') {
            return array('success' => false, 'message' => 'Form title is required');
        }

        $slug = trim(isset($body['slug']) ? $body['slug'] : '');
        if ($slug === '') {
            $slug = $this->unique_form_slug($title, $form_id);
        } else {
            $slug = $this->unique_form_slug($slug, $form_id);
        }

        $form_data = array(
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        );

        if ($form_id > 0) {
            $this->ci->db->where('id', $form_id)->update('dynamic_forms', $form_data);
        } else {
            $form_data['created_by'] = $user['user_id'];
            $form_data['created_at'] = date('Y-m-d H:i:s');
            $this->ci->db->insert('dynamic_forms', $form_data);
            $form_id = $this->ci->db->insert_id();
        }

        $this->ci->db->where('form_id', $form_id)->delete('dynamic_form_fields');
        foreach ($fields as $i => $field) {
            $label = trim(isset($field['label']) ? $field['label'] : '');
            if ($label === '') {
                continue;
            }
            $this->ci->db->insert('dynamic_form_fields', array(
                'form_id' => $form_id,
                'label' => $label,
                'field_name' => $this->make_slug($label),
                'field_type' => isset($field['field_type']) ? $field['field_type'] : 'text',
                'options' => isset($field['options']) ? $field['options'] : '',
                'is_required' => !empty($field['is_required']) ? 1 : 0,
                'row_index' => isset($field['row_index']) ? (int) $field['row_index'] : 0,
                'column_width' => isset($field['column_width']) ? (int) $field['column_width'] : 12,
                'sort_order' => $i,
            ));
        }

        return array('success' => true, 'message' => 'Form saved', 'form_id' => (int) $form_id, 'slug' => $slug);
    }

    public function delete_dynamic_form($id)
    {
        $id = (int) $id;
        if (!$id) {
            return array('success' => false, 'message' => 'Form id required');
        }
        $submissions = $this->ci->db->select('id')->where('form_id', $id)->get('dynamic_form_submissions')->result_array();
        if (!empty($submissions)) {
            $submission_ids = array_column($submissions, 'id');
            $this->ci->db->where_in('submission_id', $submission_ids)->delete('dynamic_form_submission_values');
            $this->ci->db->where_in('id', $submission_ids)->delete('dynamic_form_submissions');
        }
        $this->ci->db->where('form_id', $id)->delete('dynamic_form_fields');
        $this->ci->db->where('id', $id)->delete('dynamic_forms');
        return array('success' => true, 'message' => 'Form deleted');
    }

    public function upload_fb_leads($website, $file_path)
    {
        if ($website === '' || !is_readable($file_path)) {
            return array('success' => false, 'message' => 'Website and CSV file required');
        }

        $file = fopen($file_path, 'r');
        if (!$file) {
            return array('success' => false, 'message' => 'Could not read CSV file');
        }

        $row = 1;
        $inserted = 0;
        while (!feof($file)) {
            $index = fgetcsv($file);
            if ($row != 1 && is_array($index)) {
                $phone = str_replace('p:+92', '0', @$index[13]);
                $phone = str_replace('p:+', '0', $phone);
                $phone = str_replace('p:0', '0', @$phone);
                $date = date('Y-m-d H:i:s', strtotime(@$index[1]));
                if ($phone != '') {
                    $this->ci->db->set('date', $date);
                    $this->ci->db->set('website', $website);
                    $this->ci->db->set('name', @$index[12]);
                    $this->ci->db->set('fb_ad_name', @$index[3]);
                    $this->ci->db->set('mobile_name', @$index[12]);
                    $this->ci->db->set('campaign_name', @$index[7]);
                    $this->ci->db->set('mobile', $phone);
                    $this->ci->db->set('emergency_no', $phone);
                    $this->ci->db->insert('apply_now');
                    $inserted++;
                }
            }
            $row++;
        }
        fclose($file);

        return array('success' => true, 'message' => 'Facebook leads uploaded', 'inserted' => $inserted);
    }

    public function how_to_use_list()
    {
        if (!$this->ci->db->table_exists('how_to_use')) {
            return array();
        }
        return $this->ci->db
            ->order_by('id', 'DESC')
            ->get_where('how_to_use', array('module' => 'online_applications'))
            ->result_array();
    }

    public function how_to_use_add($body)
    {
        if (!$this->ci->db->table_exists('how_to_use')) {
            return array('success' => false, 'message' => 'how_to_use table missing');
        }
        $title = trim(isset($body['title']) ? $body['title'] : '');
        $detail = isset($body['detail']) ? $body['detail'] : '';
        if ($title === '') {
            return array('success' => false, 'message' => 'Title required');
        }
        $row = array('title' => $title, 'module' => 'online_applications');
        if ($this->ci->db->field_exists('detail', 'how_to_use')) {
            $row['detail'] = $detail;
        } elseif ($this->ci->db->field_exists('description', 'how_to_use')) {
            $row['description'] = $detail;
        }
        if ($this->ci->db->field_exists('created_at', 'how_to_use')) {
            $row['created_at'] = date('Y-m-d H:i:s');
        }
        $this->ci->db->insert('how_to_use', $row);
        return array('success' => true, 'message' => 'How-to entry added', 'id' => (int) $this->ci->db->insert_id());
    }

    public function public_form_url($slug)
    {
        return site_url('online_application/public_form/' . $slug);
    }
}
