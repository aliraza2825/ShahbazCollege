<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Course Management + Test Engine business logic for React POS (legacy parity).
 */
class Course_management_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    private function _access_row($user)
    {
        if (!$user || empty($user['user_id'])) {
            return null;
        }
        return $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
    }

    private function _is_admin($user)
    {
        return $user && isset($user['role']) && $user['role'] === 'Admin';
    }

    public function _actor_name($user)
    {
        $name = trim((isset($user['first_name']) ? $user['first_name'] : '') . ' ' . (isset($user['last_name']) ? $user['last_name'] : ''));
        return $name !== '' ? $name : 'POS';
    }

    private function _has_perm($user, $key)
    {
        if ($this->_is_admin($user)) {
            return true;
        }
        $acc = $this->_access_row($user);
        return $acc && !empty($acc[$key]);
    }

    private function _deny($message)
    {
        return array('success' => false, 'message' => $message);
    }

    private function _test_engine_subject_ids($user)
    {
        if ($this->_is_admin($user)) {
            return null;
        }
        $acc = $this->_access_row($user);
        if (!$acc || empty($acc['test_engine_subject_ids'])) {
            return array();
        }
        return array_values(array_filter(array_map('intval', explode(',', $acc['test_engine_subject_ids']))));
    }

    private function _apply_test_engine_subject_scope($alias = 'course_subjects')
    {
        $ids = $this->_test_engine_subject_ids($this->_current_user_scope());
        if ($ids === null) {
            return;
        }
        if (empty($ids)) {
            $this->ci->db->where($alias . '.course_subject_id', 0);
            return;
        }
        $this->ci->db->where_in($alias . '.course_subject_id', $ids);
    }

    /** @var array|null */
    private $_scope_user = null;

    private function _set_scope_user($user)
    {
        $this->_scope_user = $user;
    }

    private function _current_user_scope()
    {
        return $this->_scope_user;
    }

    public function permissions($user)
    {
        $is_admin = $this->_is_admin($user);
        $acc = $is_admin ? array() : ($this->_access_row($user) ?: array());

        return array(
            'is_admin' => $is_admin,
            'sidebar' => $is_admin || !empty($acc['course_management_access']),
            'add_course' => $is_admin || !empty($acc['course_management_add_course']),
            'all_course' => $is_admin || !empty($acc['course_management_all_course']),
            'edit_course' => $is_admin || !empty($acc['course_management_edit_course']),
            'delete_course' => $is_admin || !empty($acc['course_management_delete_course']),
            'add_subject' => $is_admin || !empty($acc['course_management_add_subject']),
            'all_subject' => $is_admin || !empty($acc['course_management_all_subject']),
            'edit_subject' => $is_admin || !empty($acc['course_management_edit_subject']),
            'delete_subject' => $is_admin || !empty($acc['course_management_delete_subject']),
            'add_chapter' => $is_admin || !empty($acc['course_management_add_chapter']),
            'all_chapter' => $is_admin || !empty($acc['course_management_all_chapter']),
            'edit_chapter' => $is_admin || !empty($acc['course_management_edit_chapter']),
            'delete_chapter' => $is_admin || !empty($acc['course_management_delete_chapter']),
            'add_topic' => $is_admin || !empty($acc['course_management_add_topic']),
            'all_topic' => $is_admin || !empty($acc['course_management_all_topic']),
            'edit_topic' => $is_admin || !empty($acc['course_management_edit_topic']),
            'delete_topic' => $is_admin || !empty($acc['course_management_delete_topic']),
            'add_practical_books' => $is_admin || !empty($acc['test_engine_add_practical_books']),
            'add_practical' => $is_admin || !empty($acc['test_engine_add_practical']),
            'edit_practical' => $is_admin || !empty($acc['test_engine_edit_practical']),
            'delete_practical' => $is_admin || !empty($acc['test_engine_delete_practical']),
            'books' => $is_admin || !empty($acc['test_engine_books']),
            'view_question' => $is_admin || !empty($acc['test_engine_view_question']),
            'add_questions' => $is_admin || !empty($acc['test_engine_add_questions']),
            'edit_question' => $is_admin || !empty($acc['test_engine_edit_question']),
            'delete_question' => $is_admin || !empty($acc['test_engine_delete_question']),
            'test_engine_subject_ids' => $is_admin ? null : (isset($acc['test_engine_subject_ids']) ? $acc['test_engine_subject_ids'] : ''),
        );
    }

    public function can_access($user)
    {
        $p = $this->permissions($user);
        if ($p['sidebar']) {
            return true;
        }
        foreach ($p as $k => $v) {
            if ($k === 'is_admin' || $k === 'sidebar' || $k === 'test_engine_subject_ids') {
                continue;
            }
            if ($v) {
                return true;
            }
        }
        return false;
    }

    public function meta($user)
    {
        $campuses = $this->ci->db->order_by('campus_name', 'ASC')->get('campuses')->result_array();
        $courses = $this->ci->db->order_by('course_name', 'ASC')->get_where('courses', array('status' => 1))->result_array();

        return array(
            'permissions' => $this->permissions($user),
            'campuses' => $campuses,
            'courses' => $courses,
            'legacy_root' => rtrim(base_url(), '/'),
            'uploads_root' => rtrim(base_url(), '/') . '/uploads/',
            'recording_root' => rtrim(base_url(), '/') . '/recording/',
        );
    }

    private function _recording_dir()
    {
        return FCPATH . 'recording/';
    }

    private function _question_has_audio($question)
    {
        $base = $this->_recording_dir();
        $qid = (int) $question['question_id'];
        if (file_exists($base . $qid . '.ogg')) {
            return true;
        }
        if (!empty($question['audio']) && file_exists($base . $question['audio'])) {
            return true;
        }
        return false;
    }

    private function _count_audio($questions)
    {
        $n = 0;
        foreach ($questions as $q) {
            if ($this->_question_has_audio($q)) {
                $n++;
            }
        }
        return $n;
    }

    private function _questions_by_type($topic_ids, $type)
    {
        if (empty($topic_ids)) {
            return array();
        }
        $this->ci->db->from('questions');
        $this->ci->db->where_in('topic_id', $topic_ids);
        if ($type === 'mcq') {
            $this->ci->db->where('(type = "radio" OR type = "multiple")', null, false);
        } else {
            $this->ci->db->where('type', $type);
        }
        return $this->ci->db->get()->result_array();
    }

    private function _topic_ids_for_subject($subject_id)
    {
        $topics = $this->ci->db->get_where('topics', array('course_subject_id' => (int) $subject_id))->result_array();
        $ids = array();
        foreach ($topics as $t) {
            $ids[] = (int) $t['topic_id'];
        }
        return $ids;
    }

    private function _subject_stats($subject_id)
    {
        $subject_id = (int) $subject_id;
        $topic_ids = $this->_topic_ids_for_subject($subject_id);
        $mcqs = $this->_questions_by_type($topic_ids, 'mcq');
        $short = $this->_questions_by_type($topic_ids, 'short-question');
        $long = $this->_questions_by_type($topic_ids, 'long-question');
        $words = $this->_questions_by_type($topic_ids, 'word-meaning');

        return array(
            'chapters_count' => (int) $this->ci->db->where('course_subject_id', $subject_id)->count_all_results('chapters'),
            'topics_count' => count($topic_ids),
            'mcq_count' => count($mcqs),
            'mcq_audio_count' => $this->_count_audio($mcqs),
            'short_count' => count($short),
            'short_audio_count' => $this->_count_audio($short),
            'long_count' => count($long),
            'long_audio_count' => $this->_count_audio($long),
            'word_count' => count($words),
            'word_audio_count' => $this->_count_audio($words),
        );
    }

    private function _topic_stats($topic_id)
    {
        $topic_id = (int) $topic_id;
        $topic_ids = array($topic_id);
        $mcqs = $this->_questions_by_type($topic_ids, 'mcq');
        $short = $this->_questions_by_type($topic_ids, 'short-question');
        $long = $this->_questions_by_type($topic_ids, 'long-question');
        $words = $this->_questions_by_type($topic_ids, 'word-meaning');

        return array(
            'mcq_count' => count($mcqs),
            'mcq_audio_count' => $this->_count_audio($mcqs),
            'short_count' => count($short),
            'short_audio_count' => $this->_count_audio($short),
            'long_count' => count($long),
            'long_audio_count' => $this->_count_audio($long),
            'word_count' => count($words),
            'word_audio_count' => $this->_count_audio($words),
        );
    }

    private function _normalize_campus_ids($value)
    {
        if (is_array($value)) {
            $ids = array_values(array_filter(array_map('intval', $value)));
            return implode(',', $ids);
        }
        if ($value === null || $value === '') {
            return '';
        }
        return implode(',', array_values(array_filter(array_map('intval', explode(',', (string) $value)))));
    }

    private function _normalize_extra_course_ids($value)
    {
        if (is_array($value)) {
            return implode(',', array_values(array_filter(array_map('intval', $value))));
        }
        if ($value === null || $value === '') {
            return '';
        }
        return (string) $value;
    }

    private function _checkbox_int($body, $key)
    {
        if (!isset($body[$key])) {
            return 0;
        }
        return !empty($body[$key]) ? 1 : 0;
    }

    private function _topic_context($topic_id)
    {
        $topic = $this->ci->db->get_where('topics', array('topic_id' => (int) $topic_id))->row_array();
        if (!$topic) {
            return null;
        }
        return array(
            'course_id' => (int) $topic['course_id'],
            'course_subject_id' => (int) $topic['course_subject_id'],
            'chapter_id' => (int) $topic['chapter_id'],
        );
    }

    private function _upload_file($field, $path, $allowed = '*', $index = null)
    {
        $this->ci->load->library('upload');
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        if ($index !== null && isset($_FILES[$field]['name'][$index])) {
            $_FILES['file'] = array(
                'name' => $_FILES[$field]['name'][$index],
                'type' => $_FILES[$field]['type'][$index],
                'tmp_name' => $_FILES[$field]['tmp_name'][$index],
                'error' => $_FILES[$field]['error'][$index],
                'size' => $_FILES[$field]['size'][$index],
            );
            $field = 'file';
        }
        $config = array(
            'upload_path' => $path,
            'allowed_types' => $allowed,
        );
        $this->ci->upload->initialize($config);
        if (!$this->ci->upload->do_upload($field)) {
            return '';
        }
        $data = $this->ci->upload->data();
        return !empty($data['file_name']) ? $data['file_name'] : '';
    }

    // ── Meta & cascades ─────────────────────────────────────────────────────

    public function subjects_for_course($user, $course_id, $class_period = null)
    {
        $this->_set_scope_user($user);
        $course_id = (int) $course_id;
        if ($course_id <= 0) {
            return array('success' => true, 'data' => array());
        }

        $this->ci->db->select('course_subject_id, subject_name, subject_year, subject_semester, course_id, extra_course_ids, status, add_by, last_edit');
        $this->ci->db->from('course_subjects');
        $this->ci->db->where(array('course_id' => $course_id, 'status' => 1));
        if ($class_period !== null && $class_period !== '') {
            $p = $this->ci->db->escape_str((string) $class_period);
            $this->ci->db->where("(subject_year = '{$p}' OR subject_semester = '{$p}')", null, false);
        }
        $this->_apply_test_engine_subject_scope('course_subjects');
        $this->ci->db->order_by('subject_name', 'ASC');
        return array('success' => true, 'data' => $this->ci->db->get()->result_array());
    }

    public function chapters_for_subject($subject_id)
    {
        $subject_id = (int) $subject_id;
        if ($subject_id <= 0) {
            return array('success' => true, 'data' => array());
        }
        $rows = $this->ci->db
            ->select('chapter_id, chapter_name, course_id, course_subject_id')
            ->where('course_subject_id', $subject_id)
            ->order_by('chapter_name', 'ASC')
            ->get('chapters')
            ->result_array();
        return array('success' => true, 'data' => $rows);
    }

    public function course_details($course_id)
    {
        $course_id = (int) $course_id;
        $course = $this->ci->db->get_where('courses', array('course_id' => $course_id))->row_array();
        if (!$course) {
            return array('success' => false, 'message' => 'Course not found');
        }
        $options = array();
        if ($course['course_type'] === 'Annual') {
            $years = (int) $course['course_duration_year'];
            for ($i = 1; $i <= $years; $i++) {
                $options[] = array('value' => (string) $i, 'label' => $i . ' Year');
            }
        } elseif ($course['course_type'] === 'Semester') {
            $semesters = (int) $course['course_semester'];
            for ($i = 1; $i <= $semesters; $i++) {
                $options[] = array('value' => (string) $i, 'label' => $i . ' Semester');
            }
        }
        return array(
            'success' => true,
            'data' => array(
                'course_type' => $course['course_type'],
                'course_duration_year' => (int) $course['course_duration_year'],
                'course_duration_month' => (int) $course['course_duration_month'],
                'course_semester' => (int) $course['course_semester'],
                'period_options' => $options,
            ),
        );
    }

    // ── Courses CRUD ──────────────────────────────────────────────────────────

    public function list_courses($user)
    {
        if (!$this->_has_perm($user, 'course_management_all_course')) {
            return $this->_deny('All courses permission required');
        }
        $rows = $this->ci->db->order_by('course_id', 'ASC')->get('courses')->result_array();
        foreach ($rows as &$row) {
            $row['campus_ids_list'] = $row['campus_ids'] !== '' ? array_values(array_filter(array_map('intval', explode(',', $row['campus_ids'])))) : array();
        }
        return array('success' => true, 'data' => $rows);
    }

    public function get_course($user, $course_id)
    {
        if (!$this->_has_perm($user, 'course_management_all_course') && !$this->_has_perm($user, 'course_management_edit_course')) {
            return $this->_deny('Course view permission required');
        }
        $row = $this->ci->db->get_where('courses', array('course_id' => (int) $course_id))->row_array();
        if (!$row) {
            return array('success' => false, 'message' => 'Course not found');
        }
        $row['campus_ids_list'] = $row['campus_ids'] !== '' ? array_values(array_filter(array_map('intval', explode(',', $row['campus_ids'])))) : array();
        return array('success' => true, 'data' => $row);
    }

    public function create_course($user, $body)
    {
        if (!$this->_has_perm($user, 'course_management_add_course')) {
            return $this->_deny('Add course permission required');
        }
        $name = isset($body['course_name']) ? trim($body['course_name']) : '';
        if ($name === '') {
            return $this->_deny('Course name is required');
        }
        $dup = $this->ci->db->get_where('courses', array('course_name' => $name))->row_array();
        if ($dup) {
            return $this->_deny('Course already added');
        }
        $actor = $this->_actor_name($user);
        $row = array(
            'campus_ids' => $this->_normalize_campus_ids(isset($body['campus_ids']) ? $body['campus_ids'] : ''),
            'course_name' => $name,
            'course_code' => isset($body['course_code']) ? trim($body['course_code']) : '',
            'course_type' => isset($body['course_type']) ? $body['course_type'] : 'Annual',
            'course_duration_year' => isset($body['course_duration_year']) ? (int) $body['course_duration_year'] : 0,
            'course_duration_month' => isset($body['course_duration_month']) ? (int) $body['course_duration_month'] : 0,
            'course_semester' => isset($body['course_semester']) ? (int) $body['course_semester'] : 0,
            'free' => $this->_checkbox_int($body, 'free'),
            'regular' => $this->_checkbox_int($body, 'regular'),
            'demo' => $this->_checkbox_int($body, 'demo'),
            'paid' => $this->_checkbox_int($body, 'paid'),
            'not_assigned' => $this->_checkbox_int($body, 'not_assigned'),
            'status' => isset($body['status']) ? (int) $body['status'] : 1,
            'add_by' => $actor,
            'last_edit' => $actor,
        );
        $this->ci->db->insert('courses', $row);
        return array('success' => true, 'message' => 'Course added successfully', 'data' => array('course_id' => (int) $this->ci->db->insert_id()));
    }

    public function update_course($user, $course_id, $body)
    {
        if (!$this->_has_perm($user, 'course_management_edit_course')) {
            return $this->_deny('Edit course permission required');
        }
        $course_id = (int) $course_id;
        if (!$this->ci->db->get_where('courses', array('course_id' => $course_id))->row_array()) {
            return array('success' => false, 'message' => 'Course not found');
        }
        $actor = $this->_actor_name($user);
        $row = array(
            'campus_ids' => $this->_normalize_campus_ids(isset($body['campus_ids']) ? $body['campus_ids'] : ''),
            'course_name' => isset($body['course_name']) ? trim($body['course_name']) : '',
            'course_code' => isset($body['course_code']) ? trim($body['course_code']) : '',
            'course_type' => isset($body['course_type']) ? $body['course_type'] : 'Annual',
            'course_duration_year' => isset($body['course_duration_year']) ? (int) $body['course_duration_year'] : 0,
            'course_duration_month' => isset($body['course_duration_month']) ? (int) $body['course_duration_month'] : 0,
            'course_semester' => isset($body['course_semester']) ? (int) $body['course_semester'] : 0,
            'free' => $this->_checkbox_int($body, 'free'),
            'regular' => $this->_checkbox_int($body, 'regular'),
            'demo' => $this->_checkbox_int($body, 'demo'),
            'paid' => $this->_checkbox_int($body, 'paid'),
            'not_assigned' => $this->_checkbox_int($body, 'not_assigned'),
            'status' => isset($body['status']) ? (int) $body['status'] : 1,
            'last_edit' => $actor,
        );
        $this->ci->db->where('course_id', $course_id);
        $this->ci->db->update('courses', $row);
        return array('success' => true, 'message' => 'Course updated successfully');
    }

    public function delete_course($user, $course_id)
    {
        if (!$this->_has_perm($user, 'course_management_delete_course')) {
            return $this->_deny('Delete course permission required');
        }
        $this->ci->db->where('course_id', (int) $course_id);
        $this->ci->db->delete('courses');
        return array('success' => true, 'message' => 'Course deleted successfully');
    }

    // ── Subjects CRUD ─────────────────────────────────────────────────────────

    public function list_subjects($user, $filters = array())
    {
        if (!$this->_has_perm($user, 'course_management_all_subject')) {
            return $this->_deny('All subjects permission required');
        }
        $this->ci->db->select('course_subjects.*, courses.course_name');
        $this->ci->db->from('course_subjects');
        $this->ci->db->join('courses', 'courses.course_id = course_subjects.course_id', 'left');
        $this->ci->db->where('course_subjects.status', 1);
        if (!empty($filters['course_id'])) {
            $this->ci->db->where('course_subjects.course_id', (int) $filters['course_id']);
        }
        $this->ci->db->order_by('course_subjects.course_subject_id', 'ASC');
        $rows = $this->ci->db->get()->result_array();
        return array('success' => true, 'data' => $rows);
    }

    public function get_subject($user, $id)
    {
        if (!$this->_has_perm($user, 'course_management_all_subject') && !$this->_has_perm($user, 'course_management_edit_subject')) {
            return $this->_deny('Subject view permission required');
        }
        $row = $this->ci->db->get_where('course_subjects', array('course_subject_id' => (int) $id))->row_array();
        if (!$row) {
            return array('success' => false, 'message' => 'Subject not found');
        }
        $row['extra_course_ids_list'] = $row['extra_course_ids'] !== '' ? array_values(array_filter(array_map('intval', explode(',', $row['extra_course_ids'])))) : array();
        return array('success' => true, 'data' => $row);
    }

    public function create_subject($user, $body)
    {
        if (!$this->_has_perm($user, 'course_management_add_subject')) {
            return $this->_deny('Add subject permission required');
        }
        $course_id = isset($body['course_id']) ? (int) $body['course_id'] : 0;
        $name = isset($body['subject_name']) ? trim($body['subject_name']) : '';
        if ($course_id <= 0 || $name === '') {
            return $this->_deny('Course and subject name are required');
        }
        $dup = $this->ci->db->get_where('course_subjects', array('subject_name' => $name, 'course_id' => $course_id))->row_array();
        if ($dup) {
            return $this->_deny('Subject already added');
        }
        $actor = $this->_actor_name($user);
        $row = array(
            'course_id' => $course_id,
            'extra_course_ids' => $this->_normalize_extra_course_ids(isset($body['extra_course_ids']) ? $body['extra_course_ids'] : ''),
            'subject_name' => $name,
            'subject_year' => isset($body['subject_year']) ? $body['subject_year'] : 0,
            'subject_semester' => isset($body['subject_semester']) ? $body['subject_semester'] : 0,
            'status' => isset($body['status']) ? (int) $body['status'] : 1,
            'add_by' => $actor,
            'last_edit' => $actor,
        );
        $this->ci->db->insert('course_subjects', $row);
        return array('success' => true, 'message' => 'Subject added successfully', 'data' => array('course_subject_id' => (int) $this->ci->db->insert_id()));
    }

    public function update_subject($user, $id, $body)
    {
        if (!$this->_has_perm($user, 'course_management_edit_subject')) {
            return $this->_deny('Edit subject permission required');
        }
        $id = (int) $id;
        if (!$this->ci->db->get_where('course_subjects', array('course_subject_id' => $id))->row_array()) {
            return array('success' => false, 'message' => 'Subject not found');
        }
        $actor = $this->_actor_name($user);
        $row = array(
            'course_id' => isset($body['course_id']) ? (int) $body['course_id'] : 0,
            'extra_course_ids' => $this->_normalize_extra_course_ids(isset($body['extra_course_ids']) ? $body['extra_course_ids'] : ''),
            'subject_name' => isset($body['subject_name']) ? trim($body['subject_name']) : '',
            'subject_year' => isset($body['subject_year']) ? $body['subject_year'] : 0,
            'subject_semester' => isset($body['subject_semester']) ? $body['subject_semester'] : 0,
            'status' => isset($body['status']) ? (int) $body['status'] : 1,
            'last_edit' => $actor,
        );
        $this->ci->db->where('course_subject_id', $id);
        $this->ci->db->update('course_subjects', $row);
        return array('success' => true, 'message' => 'Subject updated successfully');
    }

    public function delete_subject($user, $id)
    {
        if (!$this->_has_perm($user, 'course_management_delete_subject')) {
            return $this->_deny('Delete subject permission required');
        }
        $this->ci->db->set('status', 0);
        $this->ci->db->where('course_subject_id', (int) $id);
        $this->ci->db->update('course_subjects');
        return array('success' => true, 'message' => 'Subject deleted successfully');
    }

    // ── Chapters CRUD ─────────────────────────────────────────────────────────

    public function list_chapters($user, $filters = array())
    {
        if (!$this->_has_perm($user, 'course_management_all_chapter')) {
            return $this->_deny('All chapters permission required');
        }
        $this->ci->db->select('chapters.*, courses.course_name, course_subjects.subject_name');
        $this->ci->db->from('chapters');
        $this->ci->db->join('course_subjects', 'course_subjects.course_subject_id = chapters.course_subject_id', 'inner');
        $this->ci->db->join('courses', 'courses.course_id = chapters.course_id', 'inner');
        if (!empty($filters['course_id'])) {
            $this->ci->db->where('chapters.course_id', (int) $filters['course_id']);
        }
        if (!empty($filters['subject_id'])) {
            $this->ci->db->where('chapters.course_subject_id', (int) $filters['subject_id']);
        }
        $this->ci->db->order_by('chapters.chapter_id', 'ASC');
        return array('success' => true, 'data' => $this->ci->db->get()->result_array());
    }

    public function get_chapter($user, $id)
    {
        if (!$this->_has_perm($user, 'course_management_all_chapter') && !$this->_has_perm($user, 'course_management_edit_chapter')) {
            return $this->_deny('Chapter view permission required');
        }
        $row = $this->ci->db->get_where('chapters', array('chapter_id' => (int) $id))->row_array();
        if (!$row) {
            return array('success' => false, 'message' => 'Chapter not found');
        }
        return array('success' => true, 'data' => $row);
    }

    public function create_chapter($user, $body)
    {
        if (!$this->_has_perm($user, 'course_management_add_chapter')) {
            return $this->_deny('Add chapter permission required');
        }
        $row = array(
            'course_id' => isset($body['course_id']) ? (int) $body['course_id'] : 0,
            'course_subject_id' => isset($body['course_subject_id']) ? (int) $body['course_subject_id'] : 0,
            'chapter_name' => isset($body['chapter_name']) ? trim($body['chapter_name']) : '',
        );
        if ($row['course_id'] <= 0 || $row['course_subject_id'] <= 0 || $row['chapter_name'] === '') {
            return $this->_deny('Course, subject, and chapter name are required');
        }
        $this->ci->db->insert('chapters', $row);
        return array('success' => true, 'message' => 'Chapter inserted successfully', 'data' => array('chapter_id' => (int) $this->ci->db->insert_id()));
    }

    public function update_chapter($user, $id, $body)
    {
        if (!$this->_has_perm($user, 'course_management_edit_chapter')) {
            return $this->_deny('Edit chapter permission required');
        }
        $id = (int) $id;
        if (!$this->ci->db->get_where('chapters', array('chapter_id' => $id))->row_array()) {
            return array('success' => false, 'message' => 'Chapter not found');
        }
        $row = array(
            'course_id' => isset($body['course_id']) ? (int) $body['course_id'] : 0,
            'course_subject_id' => isset($body['course_subject_id']) ? (int) $body['course_subject_id'] : 0,
            'chapter_name' => isset($body['chapter_name']) ? trim($body['chapter_name']) : '',
        );
        $this->ci->db->where('chapter_id', $id);
        $this->ci->db->update('chapters', $row);
        return array('success' => true, 'message' => 'Chapter updated successfully');
    }

    public function delete_chapter($user, $id)
    {
        if (!$this->_has_perm($user, 'course_management_delete_chapter')) {
            return $this->_deny('Delete chapter permission required');
        }
        $this->ci->db->where('chapter_id', (int) $id);
        $this->ci->db->delete('chapters');
        return array('success' => true, 'message' => 'Chapter deleted successfully');
    }

    // ── Topics CRUD ───────────────────────────────────────────────────────────

    public function list_topics($user, $filters = array())
    {
        if (!$this->_has_perm($user, 'course_management_all_topic')) {
            return $this->_deny('All topics permission required');
        }
        $this->ci->db->select('topics.*, chapters.chapter_name, course_subjects.subject_name, courses.course_name');
        $this->ci->db->from('topics');
        $this->ci->db->join('chapters', 'chapters.chapter_id = topics.chapter_id', 'left');
        $this->ci->db->join('course_subjects', 'course_subjects.course_subject_id = topics.course_subject_id', 'left');
        $this->ci->db->join('courses', 'courses.course_id = topics.course_id', 'left');
        if (!empty($filters['course_id'])) {
            $this->ci->db->where('topics.course_id', (int) $filters['course_id']);
        }
        if (!empty($filters['subject_id'])) {
            $this->ci->db->where('topics.course_subject_id', (int) $filters['subject_id']);
        }
        if (!empty($filters['chapter_id'])) {
            $this->ci->db->where('topics.chapter_id', (int) $filters['chapter_id']);
        }
        $this->ci->db->order_by('topics.topic_id', 'ASC');
        return array('success' => true, 'data' => $this->ci->db->get()->result_array());
    }

    public function get_topic($user, $id)
    {
        if (!$this->_has_perm($user, 'course_management_all_topic') && !$this->_has_perm($user, 'course_management_edit_topic')) {
            return $this->_deny('Topic view permission required');
        }
        $row = $this->ci->db->get_where('topics', array('topic_id' => (int) $id))->row_array();
        if (!$row) {
            return array('success' => false, 'message' => 'Topic not found');
        }
        return array('success' => true, 'data' => $row);
    }

    public function create_topic($user, $body)
    {
        if (!$this->_has_perm($user, 'course_management_add_topic')) {
            return $this->_deny('Add topic permission required');
        }
        $actor = $this->_actor_name($user);
        $row = array(
            'course_id' => isset($body['course_id']) ? (int) $body['course_id'] : 0,
            'course_subject_id' => isset($body['course_subject_id']) ? (int) $body['course_subject_id'] : 0,
            'chapter_id' => isset($body['chapter_id']) ? (int) $body['chapter_id'] : 0,
            'topic_name' => isset($body['topic_name']) ? trim($body['topic_name']) : '',
            'add_by' => $actor,
            'last_edit' => $actor,
        );
        if ($row['topic_name'] === '' || $row['course_subject_id'] <= 0) {
            return $this->_deny('Subject and topic name are required');
        }
        $dup = $this->ci->db->get_where('topics', array(
            'topic_name' => $row['topic_name'],
            'course_subject_id' => $row['course_subject_id'],
            'chapter_id' => $row['chapter_id'],
            'course_id' => $row['course_id'],
        ))->row_array();
        if ($dup) {
            return $this->_deny('Topic already added');
        }
        $this->ci->db->insert('topics', $row);
        return array('success' => true, 'message' => 'Topic added successfully', 'data' => array('topic_id' => (int) $this->ci->db->insert_id()));
    }

    public function update_topic($user, $id, $body)
    {
        if (!$this->_has_perm($user, 'course_management_edit_topic')) {
            return $this->_deny('Edit topic permission required');
        }
        $id = (int) $id;
        if (!$this->ci->db->get_where('topics', array('topic_id' => $id))->row_array()) {
            return array('success' => false, 'message' => 'Topic not found');
        }
        $actor = $this->_actor_name($user);
        $row = array(
            'course_id' => isset($body['course_id']) ? (int) $body['course_id'] : 0,
            'course_subject_id' => isset($body['course_subject_id']) ? (int) $body['course_subject_id'] : 0,
            'chapter_id' => isset($body['chapter_id']) ? (int) $body['chapter_id'] : 0,
            'topic_name' => isset($body['topic_name']) ? trim($body['topic_name']) : '',
            'last_edit' => $actor,
        );
        $this->ci->db->where('topic_id', $id);
        $this->ci->db->update('topics', $row);
        return array('success' => true, 'message' => 'Topic updated successfully');
    }

    public function delete_topic($user, $id)
    {
        if (!$this->_has_perm($user, 'course_management_delete_topic')) {
            return $this->_deny('Delete topic permission required');
        }
        $this->ci->db->where('topic_id', (int) $id);
        $this->ci->db->delete('topics');
        return array('success' => true, 'message' => 'Topic deleted successfully');
    }

    // ── Practical & Books ─────────────────────────────────────────────────────

    public function search_practical_subjects($user, $course_id)
    {
        if (!$this->_has_perm($user, 'test_engine_add_practical_books') && !$this->_has_perm($user, 'test_engine_books')) {
            return $this->_deny('Practical & books permission required');
        }
        $course_id = (int) $course_id;
        if ($course_id <= 0) {
            return $this->_deny('Course is required');
        }
        $this->ci->db->select('course_subjects.*, courses.course_name');
        $this->ci->db->from('course_subjects');
        $this->ci->db->join('courses', 'courses.course_id = course_subjects.course_id', 'inner');
        $this->ci->db->where(array('course_subjects.status' => 1, 'courses.course_id' => $course_id));
        $this->ci->db->order_by('course_subjects.subject_name', 'ASC');
        $subjects = $this->ci->db->get()->result_array();
        $out = array();
        foreach ($subjects as $subject) {
            $stats = $this->_subject_stats($subject['course_subject_id']);
            $out[] = array_merge($subject, $stats);
        }
        return array('success' => true, 'data' => $out);
    }

    public function list_practicals($user, $subject_id)
    {
        if (!$this->_has_perm($user, 'test_engine_add_practical') && !$this->_has_perm($user, 'test_engine_add_practical_books')) {
            return $this->_deny('Practical view permission required');
        }
        $rows = $this->ci->db->get_where('practicals', array('subject_id' => (int) $subject_id))->result_array();
        return array('success' => true, 'data' => $rows);
    }

    public function create_practical($user, $subject_id, $body)
    {
        if (!$this->_has_perm($user, 'test_engine_add_practical')) {
            return $this->_deny('Add practical permission required');
        }
        $actor = $this->_actor_name($user);
        $row = array(
            'practical_name' => isset($body['practical_name']) ? trim($body['practical_name']) : '',
            'data' => isset($body['data']) ? $body['data'] : '',
            'subject_id' => (int) $subject_id,
            'add_by' => $actor,
            'last_edit' => $actor,
            'status' => isset($body['status']) ? (int) $body['status'] : 1,
        );
        $this->ci->db->insert('practicals', $row);
        return array('success' => true, 'message' => 'Practical data added successfully', 'data' => array('practical_id' => (int) $this->ci->db->insert_id()));
    }

    public function update_practical($user, $subject_id, $id, $body)
    {
        if (!$this->_has_perm($user, 'test_engine_edit_practical')) {
            return $this->_deny('Edit practical permission required');
        }
        $actor = $this->_actor_name($user);
        $row = array(
            'practical_name' => isset($body['practical_name']) ? trim($body['practical_name']) : '',
            'data' => isset($body['data']) ? $body['data'] : '',
            'subject_id' => (int) $subject_id,
            'last_edit' => $actor,
            'status' => isset($body['status']) ? (int) $body['status'] : 1,
        );
        $this->ci->db->where(array('practical_id' => (int) $id, 'subject_id' => (int) $subject_id));
        $this->ci->db->update('practicals', $row);
        return array('success' => true, 'message' => 'Practical data updated successfully');
    }

    public function delete_practical($user, $subject_id, $id)
    {
        if (!$this->_has_perm($user, 'test_engine_delete_practical')) {
            return $this->_deny('Delete practical permission required');
        }
        $this->ci->db->where(array('practical_id' => (int) $id, 'subject_id' => (int) $subject_id));
        $this->ci->db->delete('practicals');
        return array('success' => true, 'message' => 'Practical deleted successfully');
    }

    public function book_html($user, $subject_id)
    {
        if (!$this->_has_perm($user, 'test_engine_books')) {
            return $this->_deny('Books permission required');
        }
        $subject_id = (int) $subject_id;
        $subjects = $this->ci->db->get_where('course_subjects', array('course_subject_id' => $subject_id))->result_array();
        if (empty($subjects)) {
            return array('success' => false, 'message' => 'Subject not found');
        }
        $topics = $this->ci->db->get_where('topics', array('course_subject_id' => $subject_id))->result_array();

        ob_start();
        echo '<html><head><title>Print</title></head><body>';
        echo '<p style="color:#F00;">Note : All update questions audios &amp; videos are available on college student portal.</p>';
        echo '<h1 style="text-align:center;">' . htmlspecialchars($subjects[0]['subject_name']) . '</h1>';
        foreach ($topics as $topic) {
            $mcqs = $this->ci->db->get_where('questions', array(
                'topic_id' => $topic['topic_id'],
                'option_1 !=' => '',
                'option_2 !=' => '',
                'option_3 !=' => '',
                'option_4 !=' => '',
            ))->result_array();
            $short_questions = $this->ci->db->get_where('questions', array('topic_id' => $topic['topic_id'], 'type' => 'short-question'))->result_array();

            echo '<h1 style="text-align:center;">' . htmlspecialchars($topic['topic_name']) . '</h1>';
            echo '<h3>Total MCQs : ' . count($mcqs) . '</h3>';
            echo '<h3>Total Short Questions : ' . count($short_questions) . '</h3>';
            echo '<h2>MCQs</h2>';
            $i = 1;
            foreach ($mcqs as $mcq) {
                echo '<h4>Question ' . $i . '</h4>';
                echo '<p>' . $mcq['question'] . '</p>';
                echo 'A .' . $mcq['option_1'] . '<br />';
                echo 'B .' . $mcq['option_2'] . '<br />';
                echo 'C .' . $mcq['option_3'] . '<br />';
                echo 'D .' . $mcq['option_4'] . '<br />';
                $i++;
            }
            echo '<br /><br /><h4>Answers</h4>';
            $i = 1;
            foreach ($mcqs as $mcq) {
                echo $i . '.' . $mcq['answer'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ';
                $i++;
            }
            echo '<br /><br /><h2>Short Questions<h2>';
            $i = 1;
            foreach ($short_questions as $short_question) {
                echo '<h4>Question ' . $i . '</h4>';
                echo '<p>' . $short_question['question'] . '</p>';
                echo '<h4>Answer</h4>';
                echo '<p>' . $short_question['explanation'] . '</p>';
                $i++;
            }
        }
        echo '</body></html>';
        $html = ob_get_clean();
        return array('success' => true, 'data' => array('html' => $html));
    }

    public function subject_all_questions($user, $subject_id)
    {
        if (!$this->_has_perm($user, 'test_engine_view_question')) {
            return $this->_deny('View questions permission required');
        }
        $subject_id = (int) $subject_id;
        $join = function ($extra_where) use ($subject_id) {
            $this->ci->db->select('questions.*, chapters.chapter_name, topics.topic_name');
            $this->ci->db->from('questions');
            $this->ci->db->join('topics', 'topics.topic_id = questions.topic_id', 'inner');
            $this->ci->db->join('chapters', 'chapters.chapter_id = topics.chapter_id', 'inner');
            $this->ci->db->where('topics.course_subject_id', $subject_id);
            foreach ($extra_where as $k => $v) {
                $this->ci->db->where($k, $v);
            }
            return $this->ci->db->get()->result_array();
        };

        return array(
            'success' => true,
            'data' => array(
                'mcqs' => $join(array('option_1 !=' => '')),
                'short_questions' => $join(array('type' => 'short-question')),
                'long_questions' => $join(array('type' => 'long-question')),
                'word_meanings' => $join(array('type' => 'word-meaning')),
            ),
        );
    }

    // ── Questions hub ─────────────────────────────────────────────────────────

    public function search_question_topics($user, $course_id, $subject_id, $chapter_id, $class_id = null)
    {
        if (!$this->_has_perm($user, 'test_engine_view_question') && !$this->_has_perm($user, 'test_engine_add_questions')) {
            return $this->_deny('View questions permission required');
        }
        $this->_set_scope_user($user);
        $course_id = (int) $course_id;
        $subject_id = (int) $subject_id;
        $chapter_id = (int) $chapter_id;
        $period = $class_id;

        $this->ci->db->select('topics.*, chapters.chapter_name, course_subjects.subject_name');
        $this->ci->db->from('topics');
        $this->ci->db->join('chapters', 'topics.chapter_id = chapters.chapter_id', 'inner');
        $this->ci->db->join('course_subjects', 'course_subjects.course_subject_id = chapters.course_subject_id', 'inner');
        if ($course_id > 0) {
            $this->ci->db->where('topics.course_id', $course_id);
        }
        if ($subject_id > 0) {
            $this->ci->db->where('course_subjects.course_subject_id', $subject_id);
        }
        if ($chapter_id > 0) {
            $this->ci->db->where('chapters.chapter_id', $chapter_id);
        }
        if ($period !== null && $period !== '') {
            $p = $this->ci->db->escape_str((string) $period);
            $this->ci->db->where("(course_subjects.subject_year = '{$p}' OR course_subjects.subject_semester = '{$p}')", null, false);
        }
        $this->_apply_test_engine_subject_scope('course_subjects');
        $this->ci->db->order_by('topics.topic_id', 'ASC');
        $topics = $this->ci->db->get()->result_array();
        $out = array();
        foreach ($topics as $topic) {
            $out[] = array_merge($topic, $this->_topic_stats($topic['topic_id']));
        }
        return array('success' => true, 'data' => $out);
    }

    public function question_hub($user, $topic_id)
    {
        if (!$this->_has_perm($user, 'test_engine_view_question') && !$this->_has_perm($user, 'test_engine_add_questions')) {
            return $this->_deny('View questions permission required');
        }
        $topic_id = (int) $topic_id;
        $topics = $this->ci->db->get_where('topics', array('topic_id' => $topic_id))->result_array();
        if (empty($topics)) {
            return array('success' => false, 'message' => 'Topic not found');
        }
        $mcqs = $this->ci->db->get_where('questions', array('topic_id' => $topic_id, 'option_1 !=' => ''))->result_array();
        $short = $this->ci->db->get_where('questions', array('topic_id' => $topic_id, 'type' => 'short-question'))->result_array();
        $long = $this->ci->db->get_where('questions', array('topic_id' => $topic_id, 'type' => 'long-question'))->result_array();
        $words = $this->ci->db->get_where('questions', array('topic_id' => $topic_id, 'type' => 'word-meaning'))->result_array();
        $videos = $this->ci->db->get_where('question_videos', array('topic_id' => $topic_id))->result_array();
        $book = $this->ci->db->get_where('books', array('topic_id' => $topic_id))->result_array();

        return array(
            'success' => true,
            'data' => array(
                'topic' => $topics[0],
                'mcqs' => $mcqs,
                'short_questions' => $short,
                'long_questions' => $long,
                'word_meanings' => $words,
                'videos' => $videos,
                'topic_book' => !empty($book) ? $book[0] : null,
            ),
        );
    }

    public function save_topic_data($user, $topic_id, $data, $video = null)
    {
        if (!$this->_has_perm($user, 'test_engine_add_questions')) {
            return $this->_deny('Add questions permission required');
        }
        $topic_id = (int) $topic_id;
        $actor = $this->_actor_name($user);
        $payload = array(
            'data' => isset($data['data']) ? $data['data'] : '',
            'add_by' => $actor,
            'last_edit' => $actor,
            'status' => isset($data['status']) ? (int) $data['status'] : 1,
            'video' => $video !== null ? $video : (isset($data['video']) ? $data['video'] : ''),
        );
        $existing = $this->ci->db->get_where('books', array('topic_id' => $topic_id))->row_array();
        if ($existing) {
            $this->ci->db->where('topic_id', $topic_id);
            $this->ci->db->update('books', $payload);
        } else {
            $payload['topic_id'] = $topic_id;
            $this->ci->db->insert('books', $payload);
        }
        return array('success' => true, 'message' => 'Topic data saved successfully');
    }

    public function create_mcq($user, $topic_id, $fields, $audio_file = null)
    {
        if (!$this->_has_perm($user, 'test_engine_add_questions')) {
            return $this->_deny('Add questions permission required');
        }
        $ctx = $this->_topic_context($topic_id);
        if (!$ctx) {
            return array('success' => false, 'message' => 'Topic not found');
        }
        $actor = $this->_actor_name($user);
        $answer = isset($fields['answer']) ? $fields['answer'] : '';
        if (is_array($answer)) {
            $answer = implode(',', $answer);
        }
        $audio = $audio_file ? $audio_file : '';
        $row = array(
            'type' => isset($fields['type']) ? $fields['type'] : 'radio',
            'difficulty' => isset($fields['difficulty']) ? $fields['difficulty'] : 'easy',
            'question' => isset($fields['question']) ? $fields['question'] : '',
            'option_1' => isset($fields['option_1']) ? $fields['option_1'] : '',
            'option_2' => isset($fields['option_2']) ? $fields['option_2'] : '',
            'option_3' => isset($fields['option_3']) ? $fields['option_3'] : '',
            'option_4' => isset($fields['option_4']) ? $fields['option_4'] : '',
            'answer' => $answer,
            'explanation' => isset($fields['explanation']) ? $fields['explanation'] : '',
            'topic_id' => (int) $topic_id,
            'chapter_id' => $ctx['chapter_id'],
            'subject_id' => $ctx['course_subject_id'],
            'course_id' => $ctx['course_id'],
            'add_by' => $actor,
            'last_edit' => $actor,
            'status' => isset($fields['status']) ? (int) $fields['status'] : 0,
            'audio' => $audio,
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->ci->db->insert('questions', $row);
        return array('success' => true, 'message' => 'Question added', 'data' => array('question_id' => (int) $this->ci->db->insert_id()));
    }

    public function create_short_questions_bulk($user, $topic_id, $rows)
    {
        if (!$this->_has_perm($user, 'test_engine_add_questions')) {
            return $this->_deny('Add questions permission required');
        }
        $ctx = $this->_topic_context($topic_id);
        if (!$ctx) {
            return array('success' => false, 'message' => 'Topic not found');
        }
        $actor = $this->_actor_name($user);
        $inserted = 0;
        if (!is_array($rows)) {
            $rows = array();
        }
        foreach ($rows as $i => $row) {
            $audio = isset($row['audio']) ? $row['audio'] : '';
            $payload = array(
                'type' => 'short-question',
                'difficulty' => isset($row['difficulty']) ? $row['difficulty'] : 'easy',
                'question' => isset($row['question']) ? $row['question'] : '',
                'explanation' => isset($row['explanation']) ? $row['explanation'] : '',
                'topic_id' => (int) $topic_id,
                'chapter_id' => $ctx['chapter_id'],
                'subject_id' => $ctx['course_subject_id'],
                'course_id' => $ctx['course_id'],
                'add_by' => $actor,
                'last_edit' => $actor,
                'status' => 0,
                'audio' => $audio,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->ci->db->insert('questions', $payload);
            $inserted++;
        }
        return array('success' => true, 'message' => 'Short questions added', 'data' => array('count' => $inserted));
    }

    public function create_word_meanings($user, $topic_id, $rows)
    {
        if (!$this->_has_perm($user, 'test_engine_add_questions')) {
            return $this->_deny('Add questions permission required');
        }
        $ctx = $this->_topic_context($topic_id);
        if (!$ctx) {
            return array('success' => false, 'message' => 'Topic not found');
        }
        $actor = $this->_actor_name($user);
        $inserted = 0;
        if (!is_array($rows)) {
            $rows = array();
        }
        foreach ($rows as $row) {
            $payload = array(
                'word' => isset($row['word']) ? $row['word'] : '',
                'meaning_english' => isset($row['meaning_english']) ? $row['meaning_english'] : '',
                'meaning_urdu' => isset($row['meaning_urdu']) ? $row['meaning_urdu'] : '',
                'type' => 'word-meaning',
                'topic_id' => (int) $topic_id,
                'chapter_id' => $ctx['chapter_id'],
                'subject_id' => $ctx['course_subject_id'],
                'course_id' => $ctx['course_id'],
                'add_by' => $actor,
                'last_edit' => $actor,
                'status' => isset($row['status']) ? (int) $row['status'] : 0,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->ci->db->insert('questions', $payload);
            $inserted++;
        }
        return array('success' => true, 'message' => 'Word meanings added', 'data' => array('count' => $inserted));
    }

    public function create_videos($user, $topic_id, $rows)
    {
        if (!$this->_has_perm($user, 'test_engine_add_questions')) {
            return $this->_deny('Add questions permission required');
        }
        $topic_id = (int) $topic_id;
        $actor = $this->_actor_name($user);
        $inserted = 0;
        if (!is_array($rows)) {
            $rows = array();
        }
        foreach ($rows as $row) {
            $this->ci->db->insert('question_videos', array(
                'title' => isset($row['title']) ? $row['title'] : '',
                'file' => isset($row['file']) ? $row['file'] : '',
                'topic_id' => $topic_id,
                'created_by' => $actor,
            ));
            $inserted++;
        }
        return array('success' => true, 'message' => 'Videos added', 'data' => array('count' => $inserted));
    }

    public function import_csv($user, $topic_id, $type, $csv_tmp_path)
    {
        if (!$this->_has_perm($user, 'test_engine_add_questions')) {
            return $this->_deny('Add questions permission required');
        }
        $ctx = $this->_topic_context($topic_id);
        if (!$ctx) {
            return array('success' => false, 'message' => 'Topic not found');
        }
        if (!is_readable($csv_tmp_path)) {
            return $this->_deny('CSV file unreadable');
        }
        $actor = $this->_actor_name($user);
        $imported = 0;
        $file = fopen($csv_tmp_path, 'r');
        $row = 1;
        while (!feof($file)) {
            $index = fgetcsv($file);
            if ($row === 1 || !is_array($index) || $index[0] === '') {
                $row++;
                continue;
            }
            if ($type === 'mcqs') {
                $mcq_type = isset($index[7]) ? (int) $index[7] : 1;
                $qtype = $mcq_type === 1 ? 'radio' : 'multiple';
                $difficulty = $this->_csv_difficulty(isset($index[8]) ? (int) $index[8] : 1);
                $payload = array(
                    'type' => $qtype,
                    'question' => isset($index[1]) ? $index[1] : '',
                    'option_1' => isset($index[2]) ? $index[2] : '',
                    'option_2' => isset($index[3]) ? $index[3] : '',
                    'option_3' => isset($index[4]) ? $index[4] : '',
                    'option_4' => isset($index[5]) ? $index[5] : '',
                    'answer' => isset($index[6]) ? $index[6] : '',
                    'topic_id' => (int) $topic_id,
                    'chapter_id' => $ctx['chapter_id'],
                    'subject_id' => $ctx['course_subject_id'],
                    'course_id' => $ctx['course_id'],
                    'difficulty' => $difficulty,
                    'status' => 0,
                    'add_by' => $actor,
                    'last_edit' => $actor,
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $this->ci->db->insert('questions', $payload);
                $imported++;
            } elseif ($type === 'short-question' || $type === 'long-question') {
                $difficulty = $this->_csv_difficulty(isset($index[3]) ? (int) $index[3] : 1);
                $payload = array(
                    'type' => $type,
                    'question' => isset($index[1]) ? $index[1] : '',
                    'explanation' => isset($index[2]) ? $index[2] : '',
                    'topic_id' => (int) $topic_id,
                    'chapter_id' => $ctx['chapter_id'],
                    'subject_id' => $ctx['course_subject_id'],
                    'course_id' => $ctx['course_id'],
                    'difficulty' => $difficulty,
                    'status' => 0,
                    'add_by' => $actor,
                    'last_edit' => $actor,
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $this->ci->db->insert('questions', $payload);
                $imported++;
            } elseif ($type === 'word-meaning') {
                $payload = array(
                    'type' => 'word-meaning',
                    'word' => isset($index[1]) ? $index[1] : '',
                    'meaning_english' => isset($index[2]) ? $index[2] : '',
                    'meaning_urdu' => isset($index[3]) ? utf8_encode($index[3]) : '',
                    'topic_id' => (int) $topic_id,
                    'chapter_id' => $ctx['chapter_id'],
                    'subject_id' => $ctx['course_subject_id'],
                    'course_id' => $ctx['course_id'],
                    'status' => 0,
                    'add_by' => $actor,
                    'last_edit' => $actor,
                    'created_at' => date('Y-m-d H:i:s'),
                );
                $this->ci->db->insert('questions', $payload);
                $imported++;
            }
            $row++;
        }
        fclose($file);
        return array('success' => true, 'message' => 'Questions uploaded successfully', 'data' => array('count' => $imported));
    }

    private function _csv_difficulty($level)
    {
        if ((int) $level === 2) {
            return 'medium';
        }
        if ((int) $level === 3) {
            return 'hard';
        }
        return 'easy';
    }

    public function update_question($user, $question_id, $fields, $audio_file = null)
    {
        if (!$this->_has_perm($user, 'test_engine_edit_question')) {
            return $this->_deny('Edit question permission required');
        }
        $question_id = (int) $question_id;
        $existing = $this->ci->db->get_where('questions', array('question_id' => $question_id))->row_array();
        if (!$existing) {
            return array('success' => false, 'message' => 'Question not found');
        }
        $actor = $this->_actor_name($user);
        $answer = isset($fields['answer']) ? $fields['answer'] : $existing['answer'];
        if (is_array($answer)) {
            $answer = implode(',', $answer);
        }
        $audio = $audio_file ? $audio_file : (isset($fields['old_audio']) ? $fields['old_audio'] : $existing['audio']);
        $topic_id = isset($fields['topic']) ? (int) $fields['topic'] : (isset($fields['topic_id']) ? (int) $fields['topic_id'] : (int) $existing['topic_id']);
        $ctx = $this->_topic_context($topic_id);
        $row = array(
            'type' => isset($fields['type']) ? $fields['type'] : $existing['type'],
            'difficulty' => isset($fields['difficulty']) ? $fields['difficulty'] : $existing['difficulty'],
            'question' => isset($fields['question']) ? $fields['question'] : $existing['question'],
            'option_1' => isset($fields['option_1']) ? $fields['option_1'] : $existing['option_1'],
            'option_2' => isset($fields['option_2']) ? $fields['option_2'] : $existing['option_2'],
            'option_3' => isset($fields['option_3']) ? $fields['option_3'] : $existing['option_3'],
            'option_4' => isset($fields['option_4']) ? $fields['option_4'] : $existing['option_4'],
            'answer' => $answer,
            'explanation' => isset($fields['explanation']) ? $fields['explanation'] : $existing['explanation'],
            'topic_id' => $topic_id,
            'last_edit' => $actor,
            'status' => 0,
            'audio' => $audio,
        );
        if ($ctx) {
            $row['chapter_id'] = $ctx['chapter_id'];
            $row['subject_id'] = $ctx['course_subject_id'];
            $row['course_id'] = $ctx['course_id'];
        }
        $this->ci->db->where('question_id', $question_id);
        $this->ci->db->update('questions', $row);
        return array('success' => true, 'message' => 'Question updated successfully');
    }

    public function delete_question($user, $question_id)
    {
        if (!$this->_has_perm($user, 'test_engine_delete_question')) {
            return $this->_deny('Delete question permission required');
        }
        $this->ci->db->where('question_id', (int) $question_id);
        $this->ci->db->delete('questions');
        return array('success' => true, 'message' => 'Question deleted successfully');
    }

    public function update_word_meaning($user, $question_id, $fields)
    {
        if (!$this->_has_perm($user, 'test_engine_edit_question')) {
            return $this->_deny('Edit question permission required');
        }
        $row = array(
            'word' => isset($fields['word']) ? $fields['word'] : '',
            'meaning_english' => isset($fields['meaning_english']) ? $fields['meaning_english'] : '',
            'meaning_urdu' => isset($fields['meaning_urdu']) ? $fields['meaning_urdu'] : '',
            'status' => 0,
        );
        $this->ci->db->where(array('question_id' => (int) $question_id, 'type' => 'word-meaning'));
        $this->ci->db->update('questions', $row);
        return array('success' => true, 'message' => 'Word meaning updated successfully');
    }

    public function delete_word_meaning($user, $question_id)
    {
        if (!$this->_has_perm($user, 'test_engine_delete_question')) {
            return $this->_deny('Delete question permission required');
        }
        $this->ci->db->where(array('question_id' => (int) $question_id, 'type' => 'word-meaning'));
        $this->ci->db->delete('questions');
        return array('success' => true, 'message' => 'Word meaning deleted successfully');
    }

    public function update_video($user, $video_id, $fields)
    {
        if (!$this->_has_perm($user, 'test_engine_edit_question')) {
            return $this->_deny('Edit question permission required');
        }
        $row = array(
            'title' => isset($fields['title']) ? $fields['title'] : '',
            'file' => isset($fields['file']) ? $fields['file'] : '',
        );
        $this->ci->db->where('id', (int) $video_id);
        $this->ci->db->update('question_videos', $row);
        return array('success' => true, 'message' => 'Video updated successfully');
    }

    public function delete_video($user, $video_id)
    {
        if (!$this->_has_perm($user, 'test_engine_delete_question')) {
            return $this->_deny('Delete question permission required');
        }
        $this->ci->db->where('id', (int) $video_id);
        $this->ci->db->delete('question_videos');
        return array('success' => true, 'message' => 'Video deleted successfully');
    }

    public function recheck_topic($user, $topic_id)
    {
        if (!$this->_has_perm($user, 'test_engine_view_question') && !$this->_has_perm($user, 'test_engine_add_questions')) {
            return $this->_deny('Recheck permission required');
        }
        $this->ci->db->set('status', 0);
        $this->ci->db->where('topic_id', (int) $topic_id);
        $this->ci->db->update('questions');
        return array('success' => true, 'message' => 'Topic questions sent for re-check');
    }

    public function update_question_status($user, $question_id, $status)
    {
        if (!$this->_has_perm($user, 'test_engine_edit_question') && !$this->_has_perm($user, 'test_engine_view_question')) {
            return $this->_deny('Question status permission required');
        }
        $this->ci->db->set('test_status', (int) $status);
        $this->ci->db->where('question_id', (int) $question_id);
        $this->ci->db->update('questions');
        return array('success' => true, 'message' => 'Question status updated');
    }

    public function upload_image($user, $file_field = 'image')
    {
        if (!$this->_has_perm($user, 'test_engine_add_questions')) {
            return $this->_deny('Upload permission required');
        }
        $name = $this->_upload_file($file_field, FCPATH . 'uploads/', 'gif|jpg|png|jpeg');
        if ($name === '') {
            return $this->_deny('Error uploading image');
        }
        $url = rtrim(base_url(), '/') . '/uploads/' . $name;
        return array('success' => true, 'data' => array('url' => $url, 'file' => $name));
    }

    public function upload_recording($field = 'audio', $index = null)
    {
        return $this->_upload_file($field, $this->_recording_dir(), '*', $index);
    }
}
