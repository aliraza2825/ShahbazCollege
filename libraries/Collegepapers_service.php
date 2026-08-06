<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Collegepapers_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function can_access($user)
    {
        if (!$user) {
            return false;
        }
        if ($user['role'] === 'Admin') {
            return true;
        }
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        return $acc && !empty($acc['papers_results_sidebar']);
    }

    public function permissions($user)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : ($this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array() ?: array());

        return array(
            'is_admin' => $is_admin,
            'sidebar' => $is_admin || !empty($acc['papers_results_sidebar']),
            'add_paper' => $is_admin || !empty($acc['papers_results_add_paper']),
            'all_paper' => $is_admin || !empty($acc['papers_results_all_paper']),
            'view_paper' => $is_admin || !empty($acc['papers_results_view_paper']),
            'add_result' => $is_admin || !empty($acc['papers_results_add_result']),
            'student_results' => $is_admin || !empty($acc['papers_results_student_results']) || !empty($acc['papers_results_all_paper']),
            'test_system' => $is_admin || !empty($acc['test_system']),
            'improvement_report' => $is_admin || !empty($acc['improvement_report']),
        );
    }

    public function meta($user)
    {
        return array(
            'permissions' => $this->permissions($user),
            'campuses' => $this->ci->db->order_by('campus_name', 'ASC')->get_where('campuses', array('status' => 1))->result_array(),
            'courses' => $this->ci->db->order_by('course_name', 'ASC')->get_where('courses', array('status' => 1))->result_array(),
            'exams' => $this->ci->db->order_by('id', 'DESC')->get('monthly_test_exams')->result_array(),
            'legacy_root' => rtrim(base_url(), '/'),
        );
    }

    public function subjects_for_course($course_id, $period = null, $user = null)
    {
        $course_id = (int) $course_id;
        if ($course_id <= 0) {
            return array();
        }

        $this->ci->db->select('course_subject_id, subject_name, subject_year, subject_semester');
        $this->ci->db->from('course_subjects');
        $this->ci->db->where(array('course_id' => $course_id, 'status' => 1));
        if ($period !== null && $period !== '') {
            $p = $this->ci->db->escape_str((string) $period);
            $this->ci->db->where("(subject_year = '{$p}' OR subject_semester = '{$p}')", null, false);
        }
        if ($user && $user['role'] !== 'Admin') {
            $acc = $this->ci->db->query(
                'SELECT assignment_subject_ids FROM access WHERE user_id = ?',
                array((int) $user['user_id'])
            )->row_array();
            if (!empty($acc['assignment_subject_ids'])) {
                $ids = array_values(array_filter(array_map('intval', explode(',', $acc['assignment_subject_ids']))));
                if (!empty($ids)) {
                    $this->ci->db->where_in('course_subject_id', $ids);
                }
            }
        }
        $this->ci->db->order_by('subject_name', 'ASC');
        return $this->ci->db->get()->result_array();
    }

    public function students_for_course($course_id)
    {
        $course_id = (int) $course_id;
        if ($course_id <= 0) {
            return array();
        }
        return $this->ci->db
            ->select('student_id, roll_no, first_name, last_name')
            ->where(array('status' => 1, 'course_id' => $course_id))
            ->order_by('roll_no', 'ASC')
            ->get('students')
            ->result_array();
    }

    public function all_papers($filters = array())
    {
        $course_id = isset($filters['course_id']) ? trim((string) $filters['course_id']) : '';
        $subject_id = isset($filters['subject_id']) ? trim((string) $filters['subject_id']) : '';
        $campus_id = isset($filters['campus_id']) ? (int) $filters['campus_id'] : 0;
        $start_date = !empty($filters['start_date']) ? $filters['start_date'] : date('Y-m-d');
        $end_date = !empty($filters['end_date']) ? $filters['end_date'] : date('Y-m-d');

        $this->ci->db->select('collegepapers.*, campuses.campus_name, courses.course_name');
        $this->ci->db->from('collegepapers');
        $this->ci->db->join('campuses', 'campuses.campus_id = collegepapers.campus_id', 'inner');
        $this->ci->db->join('courses', 'courses.course_id = collegepapers.course_id', 'inner');
        if ($campus_id > 0) {
            $this->ci->db->where('collegepapers.campus_id', $campus_id);
        }
        if ($course_id !== '') {
            $this->ci->db->where('collegepapers.course_id', $course_id);
        }
        if ($subject_id !== '') {
            $sid = $this->ci->db->escape_str($subject_id);
            $this->ci->db->where(
                "(collegepapers.subject_id = '{$sid}' OR collegepapers.subject_id LIKE '%,{$sid},%' OR collegepapers.subject_id LIKE '{$sid},%' OR collegepapers.subject_id LIKE '%,{$sid}')",
                null,
                false
            );
        }
        $this->ci->db->where('collegepapers.date >=', $start_date);
        $this->ci->db->where('collegepapers.date <=', $end_date);
        $this->ci->db->order_by('collegepapers.collegepaper_id', 'DESC');
        $rows = $this->ci->db->get()->result_array();

        return array_map(array($this, 'enrich_paper_row'), $rows);
    }

    public function student_results($filters = array())
    {
        $course_id = isset($filters['course_id']) ? trim((string) $filters['course_id']) : '';
        $subject_id = isset($filters['subject_id']) ? trim((string) $filters['subject_id']) : '';
        $student_id = isset($filters['student_id']) ? (int) $filters['student_id'] : 0;
        $start_date = !empty($filters['start_date']) ? $filters['start_date'] : date('Y-m-d');
        $end_date = !empty($filters['end_date']) ? $filters['end_date'] : date('Y-m-d');

        if ($course_id === '') {
            return array();
        }

        $this->ci->db->select(
            'collegepapers.*, collegepaper_results.*, classes.name as class_name, campuses.campus_name, courses.course_name, students.roll_no, students.first_name, students.last_name'
        );
        $this->ci->db->from('collegepaper_results');
        $this->ci->db->join('collegepapers', 'collegepapers.collegepaper_id = collegepaper_results.collegepaper_id', 'inner');
        $this->ci->db->join('students', 'students.student_id = collegepaper_results.student_id', 'inner');
        $this->ci->db->join('classes', 'students.class_id = classes.class_id', 'inner');
        $this->ci->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'inner');
        $this->ci->db->join('courses', 'courses.course_id = students.course_id', 'inner');
        $this->ci->db->where('collegepapers.course_id', $course_id);
        $this->ci->db->where('collegepapers.date >=', $start_date);
        $this->ci->db->where('collegepapers.date <=', $end_date);
        if ($subject_id !== '') {
            $sid = $this->ci->db->escape_str($subject_id);
            $this->ci->db->where(
                "(collegepapers.subject_id = '{$sid}' OR collegepapers.subject_id LIKE '%,{$sid},%' OR collegepapers.subject_id LIKE '{$sid},%' OR collegepapers.subject_id LIKE '%,{$sid}')",
                null,
                false
            );
        }
        if ($student_id > 0) {
            $this->ci->db->where('collegepaper_results.student_id', $student_id);
        }
        $this->ci->db->order_by('collegepapers.date', 'DESC');
        return $this->ci->db->get()->result_array();
    }

    private function enrich_paper_row($paper)
    {
        $paper['subject_names'] = $this->names_from_ids('course_subjects', 'course_subject_id', 'subject_name', $paper['subject_id']);
        $paper['topic_names'] = $this->names_from_ids('topics', 'topic_id', 'topic_name', $paper['topic_ids']);
        $paper['practical_names'] = $this->names_from_ids('practicals', 'practical_id', 'practical_name', $paper['practicals']);
        $paper['mcq_count'] = $this->csv_count($paper['mcqs']);
        $paper['short_q_count'] = $this->csv_count($paper['short_questions']);
        $paper['practical_count'] = $this->csv_count($paper['practicals']);

        $this->ci->db->select('students.roll_no');
        $this->ci->db->from('collegepaper_results');
        $this->ci->db->join('students', 'students.student_id = collegepaper_results.student_id', 'inner');
        $this->ci->db->where('collegepaper_results.collegepaper_id', $paper['collegepaper_id']);
        $rolls = $this->ci->db->get()->result_array();
        $paper['result_count'] = count($rolls);
        $paper['result_roll_nos'] = array_values(array_filter(array_map(function ($r) {
            return $r['roll_no'];
        }, $rolls)));

        return $paper;
    }

    private function csv_count($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        return count(array_filter(explode(',', (string) $value), 'strlen'));
    }

    private function names_from_ids($table, $idCol, $nameCol, $csv)
    {
        if ($csv === null || $csv === '') {
            return array();
        }
        $ids = array_values(array_filter(array_map('intval', explode(',', (string) $csv))));
        if (empty($ids)) {
            return array();
        }
        $rows = $this->ci->db->where_in($idCol, $ids)->get($table)->result_array();
        $map = array();
        foreach ($rows as $row) {
            $map[(int) $row[$idCol]] = $row[$nameCol];
        }
        $names = array();
        foreach ($ids as $id) {
            if (isset($map[$id])) {
                $names[] = $map[$id];
            }
        }
        return $names;
    }

    // ── Add paper helpers ─────────────────────────────────────────────────────

    public function sessions_for_campus($campus_id)
    {
        $this->ci->db->select('session');
        $this->ci->db->distinct();
        $this->ci->db->from('classes');
        if ((int) $campus_id > 0) {
            $this->ci->db->where('campus_id', (int) $campus_id);
        } else {
            $this->ci->db->where('campus_id >', 0);
        }
        $this->ci->db->order_by('session', 'ASC');
        $rows = $this->ci->db->get()->result_array();
        $out = array();
        foreach ($rows as $row) {
            if (!empty($row['session'])) {
                $out[] = array('value' => $row['session'], 'label' => $row['session']);
            }
        }
        return $out;
    }

    public function course_period_options($course_id)
    {
        $course_id = (int) $course_id;
        if ($course_id <= 0) {
            return array('course_type' => '', 'options' => array());
        }
        $course = $this->ci->db->get_where('courses', array('course_id' => $course_id))->row_array();
        if (!$course) {
            return array('course_type' => '', 'options' => array());
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
        return array('course_type' => $course['course_type'], 'options' => $options);
    }

    public function chapters_for_subjects($subject_ids)
    {
        $ids = $this->normalize_id_list($subject_ids);
        if (empty($ids)) {
            return array();
        }
        return $this->ci->db
            ->select('chapter_id, chapter_name, course_subject_id')
            ->where_in('course_subject_id', $ids)
            ->order_by('chapter_name', 'ASC')
            ->get('chapters')
            ->result_array();
    }

    public function topics_for_chapters($chapter_ids)
    {
        $ids = $this->normalize_id_list($chapter_ids);
        if (empty($ids)) {
            return array();
        }
        return $this->ci->db
            ->select('topic_id, topic_name, chapter_id, course_subject_id')
            ->where_in('chapter_id', $ids)
            ->order_by('topic_name', 'ASC')
            ->get('topics')
            ->result_array();
    }

    public function practicals_for_subjects($subject_ids)
    {
        $ids = $this->normalize_id_list($subject_ids);
        if (empty($ids)) {
            return array();
        }
        return $this->ci->db
            ->select('practical_id, practical_name, subject_id')
            ->where_in('subject_id', $ids)
            ->where('status', 1)
            ->order_by('practical_name', 'ASC')
            ->get('practicals')
            ->result_array();
    }

    public function classes_for_campus_course($campus_id, $course_id)
    {
        $this->ci->db->select('class_id, name, session, exam_no');
        $this->ci->db->from('classes');
        $this->ci->db->where('status', 1);
        if ((int) $campus_id > 0) {
            $this->ci->db->where('campus_id', (int) $campus_id);
        }
        if ((int) $course_id > 0) {
            $this->ci->db->where('course_id', (int) $course_id);
        }
        $this->ci->db->order_by('name', 'ASC');
        return $this->ci->db->get()->result_array();
    }

    public function expense_categories_flat()
    {
        return $this->ci->db
            ->select('expense_category_id, name, sub_of, status')
            ->where('status', 'active')
            ->order_by('name', 'ASC')
            ->get('expense_category')
            ->result_array();
    }

    public function prepare_create_paper($user, $payload)
    {
        $this->sync_legacy_session($user);

        $topic_id = isset($payload['topic_id']) ? $payload['topic_id'] : array();
        $subject_id = isset($payload['subject_id']) ? $payload['subject_id'] : array();
        $chapter_id = isset($payload['chapter_id']) ? $payload['chapter_id'] : array();
        $class_session = isset($payload['class_session']) ? $payload['class_session'] : array();
        $practical_id = isset($payload['practical_id']) ? $payload['practical_id'] : array();

        if (!is_array($topic_id)) {
            $topic_id = array_filter(array_map('intval', explode(',', (string) $topic_id)));
        }
        if (!is_array($subject_id)) {
            $subject_id = array_filter(array_map('intval', explode(',', (string) $subject_id)));
        }
        if (!is_array($chapter_id)) {
            $chapter_id = array_filter(array_map('intval', explode(',', (string) $chapter_id)));
        }
        if (!is_array($class_session)) {
            $class_session = array($class_session);
        }
        if (!is_array($practical_id)) {
            $practical_id = array_filter(array_map('intval', explode(',', (string) $practical_id)));
        }

        $this->ci->session->set_userdata(array(
            'college_paper_topic_id' => $topic_id,
            'college_paper_campus_id' => isset($payload['campus_id']) ? $payload['campus_id'] : null,
            'college_paper_session' => $class_session,
            'college_paper_subject_id' => $subject_id,
            'college_paper_chapter_id' => $chapter_id,
            'college_paper_section' => isset($payload['section']) ? $payload['section'] : null,
            'college_paper_course_id' => isset($payload['course_id']) ? $payload['course_id'] : null,
            'college_paper_class' => isset($payload['class']) ? $payload['class'] : null,
            'college_paper_exam_id' => isset($payload['exam_id']) ? $payload['exam_id'] : null,
            'college_paper_attendence_wise' => !empty($payload['attendence_wise']) ? 1 : 0,
            'college_paper_mcqs' => isset($payload['mcqs']) ? (int) $payload['mcqs'] : 0,
            'college_paper_marks_mcq' => isset($payload['marks_mcq']) ? (int) $payload['marks_mcq'] : 0,
            'college_paper_short_questions' => isset($payload['short_questions']) ? (int) $payload['short_questions'] : 0,
            'college_paper_short_question_mcq' => isset($payload['short_question_mcq']) ? (int) $payload['short_question_mcq'] : 0,
            'college_paper_marks_practical' => isset($payload['marks_practical']) ? (int) $payload['marks_practical'] : 0,
            'college_paper_practical_id' => $practical_id,
            'college_paper_short_question_lines' => isset($payload['short_question_lines']) ? (int) $payload['short_question_lines'] : 0,
            'college_paper_practical_lines' => isset($payload['practical_lines']) ? (int) $payload['practical_lines'] : 0,
        ));

        return rtrim(base_url(), '/') . '/index.php/collegepapers/create_paper';
    }

    // ── Monthly test system ───────────────────────────────────────────────────

    public function list_exams()
    {
        return $this->ci->db->order_by('id', 'DESC')->get('monthly_test_exams')->result_array();
    }

    public function get_exam($id)
    {
        $exam = $this->ci->db->where('id', (int) $id)->get('monthly_test_exams')->row_array();
        if ($exam && !empty($exam['expense_category'])) {
            $decoded = json_decode($exam['expense_category'], true);
            $exam['expense_category_ids'] = is_array($decoded) ? $decoded : array();
        } else {
            $exam['expense_category_ids'] = array();
        }
        return $exam;
    }

    public function save_exam($data)
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $chain = isset($data['expense_category_id']) ? $data['expense_category_id'] : array();
        if (!is_array($chain)) {
            $chain = array($chain);
        }
        $chain = array_values(array_filter(array_map('intval', $chain)));

        $row = array(
            'exam_name' => isset($data['exam_name']) ? $data['exam_name'] : '',
            'expense_category' => json_encode($chain),
            'description' => isset($data['description']) ? $data['description'] : '',
            'status' => isset($data['status']) ? (int) $data['status'] : 1,
            'updated_at' => date('Y-m-d H:i:s'),
        );

        if ($id > 0) {
            $this->ci->db->where('id', $id)->update('monthly_test_exams', $row);
            return array('id' => $id, 'message' => 'Exam updated successfully');
        }

        $row['created_at'] = date('Y-m-d H:i:s');
        $this->ci->db->insert('monthly_test_exams', $row);
        return array('id' => (int) $this->ci->db->insert_id(), 'message' => 'Exam added successfully');
    }

    public function delete_exam($id)
    {
        $this->ci->db->where('id', (int) $id)->delete('monthly_test_exams');
        return array('message' => 'Exam deleted successfully');
    }

    public function get_improvement_rules($exam_id)
    {
        return $this->ci->db
            ->where('exam_id', (int) $exam_id)
            ->order_by('attempt_no', 'ASC')
            ->get('monthly_test_improvement_rules')
            ->result_array();
    }

    public function get_improvement_rule($id)
    {
        return $this->ci->db->where('id', (int) $id)->get('monthly_test_improvement_rules')->row_array();
    }

    public function save_improvement_rule($data)
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $row = array(
            'exam_id' => (int) $data['exam_id'],
            'attempt_no' => (int) $data['attempt_no'],
            'attempt_name' => isset($data['attempt_name']) ? $data['attempt_name'] : '',
            'min_percentage' => isset($data['min_percentage']) && $data['min_percentage'] !== '' ? $data['min_percentage'] : 0,
            'max_percentage' => isset($data['max_percentage']) && $data['max_percentage'] !== '' ? $data['max_percentage'] : null,
            'improvement_required' => !empty($data['improvement_required']) ? 1 : 0,
            'status' => isset($data['status']) ? (int) $data['status'] : 1,
            'updated_at' => date('Y-m-d H:i:s'),
        );

        if ($id > 0) {
            $this->ci->db->where('id', $id)->update('monthly_test_improvement_rules', $row);
            return array('id' => $id, 'message' => 'Improvement rule updated successfully');
        }

        $row['created_at'] = date('Y-m-d H:i:s');
        $this->ci->db->insert('monthly_test_improvement_rules', $row);
        return array('id' => (int) $this->ci->db->insert_id(), 'message' => 'Improvement rule added successfully');
    }

    public function delete_improvement_rule($id)
    {
        $this->ci->db->where('id', (int) $id)->delete('monthly_test_improvement_rules');
        return array('message' => 'Improvement rule deleted successfully');
    }

    public function get_reward_rules($exam_id)
    {
        return $this->ci->db
            ->where('exam_id', (int) $exam_id)
            ->order_by('improvement_count', 'ASC')
            ->get('monthly_test_reward_rules')
            ->result_array();
    }

    public function get_reward_rule($id)
    {
        return $this->ci->db->where('id', (int) $id)->get('monthly_test_reward_rules')->row_array();
    }

    public function save_reward_rule($data)
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $row = array(
            'exam_id' => (int) $data['exam_id'],
            'improvement_count' => (int) $data['improvement_count'],
            'certificate' => !empty($data['certificate']) ? 1 : 0,
            'cash_amount' => isset($data['cash_amount']) && $data['cash_amount'] !== '' ? $data['cash_amount'] : 0,
            'status' => isset($data['status']) ? (int) $data['status'] : 1,
            'updated_at' => date('Y-m-d H:i:s'),
        );

        if ($id > 0) {
            $this->ci->db->where('id', $id)->update('monthly_test_reward_rules', $row);
            return array('id' => $id, 'message' => 'Reward rule updated successfully');
        }

        $row['created_at'] = date('Y-m-d H:i:s');
        $this->ci->db->insert('monthly_test_reward_rules', $row);
        return array('id' => (int) $this->ci->db->insert_id(), 'message' => 'Reward rule added successfully');
    }

    public function delete_reward_rule($id)
    {
        $this->ci->db->where('id', (int) $id)->delete('monthly_test_reward_rules');
        return array('message' => 'Reward rule deleted successfully');
    }

    public function improvement_report($filters = array())
    {
        $exam_id = isset($filters['exam_id']) ? $filters['exam_id'] : '';
        $class = isset($filters['class']) ? $filters['class'] : '';
        $course_id = isset($filters['course_id']) ? $filters['course_id'] : '';
        $campus_id = isset($filters['campus_id']) ? $filters['campus_id'] : '';
        $badge_id = isset($filters['badge']) ? $filters['badge'] : '';

        $results = array();
        if (!empty($filters['search'])) {
            $badge = array();
            if (!empty($badge_id)) {
                $badge = $this->ci->db->get_where('classes', array('class_id' => (int) $badge_id))->row_array();
            }

            $this->ci->db->select('
                cp.collegepaper_id,
                cp.exam_id,
                cp.class as paper_class,
                cp.date,
                cp.total_marks,
                cp.course_id,
                cp.campus_id,
                cp.session,
                mt.exam_name,
                cpr.student_id,
                cpr.obtain_marks,
                s.first_name,
                s.last_name,
                s.class_id,
                cls.name as class_name
            ');
            $this->ci->db->from('collegepaper_results cpr');
            $this->ci->db->join('collegepapers cp', 'cp.collegepaper_id = cpr.collegepaper_id', 'inner');
            $this->ci->db->join('monthly_test_exams mt', 'mt.id = cp.exam_id', 'left');
            $this->ci->db->join('students s', 's.student_id = cpr.student_id', 'left');
            $this->ci->db->join('classes cls', 'cls.class_id = s.class_id', 'left');

            if ($exam_id !== '' && $exam_id !== null) {
                $this->ci->db->where('cp.exam_id', (int) $exam_id);
            }
            if ($class !== '' && $class !== null) {
                $this->ci->db->where('cp.class', $class);
            }
            if ($campus_id !== '' && $campus_id !== null) {
                $this->ci->db->where('cp.campus_id', (int) $campus_id);
            }
            if ($course_id !== '' && $course_id !== null) {
                $this->ci->db->where('cp.course_id', (int) $course_id);
            }
            if (!empty($badge)) {
                $this->ci->db->where('cp.session', $badge['session']);
            }

            $this->ci->db->order_by('cpr.student_id', 'ASC');
            $this->ci->db->order_by('cp.exam_id', 'ASC');
            $this->ci->db->order_by('YEAR(cp.date)', 'ASC', false);
            $this->ci->db->order_by('MONTH(cp.date)', 'ASC', false);
            $this->ci->db->order_by('cp.date', 'ASC');
            $this->ci->db->order_by('cp.collegepaper_id', 'ASC');

            $results = $this->ci->db->get()->result_array();
        }

        $report = $this->build_improvement_report_rows($results);
        $max_attempts = 0;
        foreach ($report as $r) {
            $count = count($r['attempts']);
            if ($count > $max_attempts) {
                $max_attempts = $count;
            }
        }

        return array(
            'report' => $report,
            'max_attempts' => $max_attempts,
        );
    }

    public function improvement_month_detail($student_id, $exam_id, $month_key)
    {
        $start_date = date('Y-m-01', strtotime($month_key . '-01'));
        $end_date = date('Y-m-t', strtotime($month_key . '-01'));

        $this->ci->db->select('
            cp.collegepaper_id,
            cp.date,
            cp.exam_id,
            cp.subject_id,
            cp.total_marks,
            mt.exam_name,
            cpr.obtain_marks,
            s.first_name,
            s.last_name,
            sub.name as subject_name
        ');
        $this->ci->db->from('collegepaper_results cpr');
        $this->ci->db->join('collegepapers cp', 'cp.collegepaper_id = cpr.collegepaper_id', 'inner');
        $this->ci->db->join('monthly_test_exams mt', 'mt.id = cp.exam_id', 'left');
        $this->ci->db->join('students s', 's.student_id = cpr.student_id', 'left');
        $this->ci->db->join('subjects sub', 'sub.subject_id = cp.subject_id', 'left');
        $this->ci->db->where('cpr.student_id', (int) $student_id);
        $this->ci->db->where('cp.exam_id', (int) $exam_id);
        $this->ci->db->where('cp.date >=', $start_date);
        $this->ci->db->where('cp.date <=', $end_date);
        $this->ci->db->order_by('cp.date', 'ASC');
        $this->ci->db->order_by('cp.collegepaper_id', 'ASC');

        $details = $this->ci->db->get()->result_array();
        $total_obtain = 0;
        $total_marks = 0;
        $enriched = array();

        foreach ($details as $row) {
            $total_obtain += (float) $row['obtain_marks'];
            $total_marks += (float) $row['total_marks'];
            $pct = 0;
            if ($row['total_marks'] > 0) {
                $pct = round(((float) $row['obtain_marks'] / (float) $row['total_marks']) * 100, 2);
            }
            $row['percentage'] = $pct;
            $row['grade'] = $this->grade_for_percentage($pct);
            $enriched[] = $row;
        }

        $percentage = 0;
        if ($total_marks > 0) {
            $percentage = round(($total_obtain / $total_marks) * 100, 2);
        }

        $student_name = '';
        $exam_name = '';
        if (!empty($details)) {
            $student_name = trim($details[0]['first_name'] . ' ' . $details[0]['last_name']);
            $exam_name = $details[0]['exam_name'];
        }

        return array(
            'details' => $enriched,
            'student_id' => (int) $student_id,
            'exam_id' => (int) $exam_id,
            'month_key' => $month_key,
            'month_name' => date('F Y', strtotime($month_key . '-01')),
            'student_name' => $student_name,
            'exam_name' => $exam_name,
            'total_obtain' => $total_obtain,
            'total_marks' => $total_marks,
            'percentage' => $percentage,
        );
    }

    public function overall_class_performance($filters = array())
    {
        $exam_id = isset($filters['exam_id']) ? $filters['exam_id'] : '';
        $class = isset($filters['class']) ? $filters['class'] : '';
        $course_id = isset($filters['course_id']) ? $filters['course_id'] : '';
        $campus_id = isset($filters['campus_id']) ? $filters['campus_id'] : '';
        $badge_id = isset($filters['badge']) ? $filters['badge'] : '';

        $badge = array();
        if (!empty($badge_id)) {
            $badge = $this->ci->db->get_where('classes', array('class_id' => (int) $badge_id))->row_array();
        }

        $this->ci->db->select('
            cp.collegepaper_id,
            cp.exam_id,
            cp.class as paper_class,
            cp.date,
            cp.total_marks,
            cp.course_id,
            cp.campus_id,
            cp.session,
            mt.exam_name,
            cpr.student_id,
            cpr.obtain_marks,
            s.first_name,
            s.last_name,
            s.class_id,
            cls.name as class_name
        ');
        $this->ci->db->from('collegepaper_results cpr');
        $this->ci->db->join('collegepapers cp', 'cp.collegepaper_id = cpr.collegepaper_id', 'inner');
        $this->ci->db->join('monthly_test_exams mt', 'mt.id = cp.exam_id', 'left');
        $this->ci->db->join('students s', 's.student_id = cpr.student_id', 'left');
        $this->ci->db->join('classes cls', 'cls.class_id = s.class_id', 'left');

        if ($exam_id !== '' && $exam_id !== null) {
            $this->ci->db->where('cp.exam_id', (int) $exam_id);
        }
        if ($class !== '' && $class !== null) {
            $this->ci->db->where('cp.class', $class);
        }
        if ($campus_id !== '' && $campus_id !== null) {
            $this->ci->db->where('cp.campus_id', (int) $campus_id);
        }
        if ($course_id !== '' && $course_id !== null) {
            $this->ci->db->where('cp.course_id', (int) $course_id);
        }
        if (!empty($badge)) {
            $this->ci->db->where('cp.session', $badge['session']);
        }

        $this->ci->db->order_by('cp.date', 'ASC');
        $this->ci->db->order_by('cpr.student_id', 'ASC');
        $results = $this->ci->db->get()->result_array();

        $monthly = array();
        foreach ($results as $row) {
            $monthKey = date('Y-m', strtotime($row['date']));
            $monthName = date('F Y', strtotime($row['date']));
            $key = $row['exam_id'] . '_' . $row['paper_class'] . '_' . $monthKey;

            if (!isset($monthly[$key])) {
                $monthly[$key] = array(
                    'month_key' => $monthKey,
                    'month_name' => $monthName,
                    'class' => !empty($row['class_name']) ? $row['class_name'] : $row['paper_class'],
                    'exam_id' => $row['exam_id'],
                    'exam_name' => !empty($row['exam_name']) ? $row['exam_name'] : '-',
                    'students' => array(),
                );
            }

            $studentId = $row['student_id'];
            if (!isset($monthly[$key]['students'][$studentId])) {
                $monthly[$key]['students'][$studentId] = array(
                    'obtain_marks' => 0,
                    'total_marks' => 0,
                );
            }

            $monthly[$key]['students'][$studentId]['obtain_marks'] += (float) $row['obtain_marks'];
            $monthly[$key]['students'][$studentId]['total_marks'] += (float) $row['total_marks'];
        }

        $report = array();
        foreach ($monthly as $monthData) {
            $appeared = count($monthData['students']);
            $passed = 0;
            $failed = 0;
            $sumPercentage = 0;
            $highest = 0;
            $lowest = 0;
            $first = true;

            foreach ($monthData['students'] as $student) {
                $percentage = 0;
                if ($student['total_marks'] > 0) {
                    $percentage = round(($student['obtain_marks'] / $student['total_marks']) * 100, 2);
                }
                $sumPercentage += $percentage;
                if ($percentage >= 50) {
                    $passed++;
                } else {
                    $failed++;
                }
                if ($first) {
                    $highest = $percentage;
                    $lowest = $percentage;
                    $first = false;
                } else {
                    if ($percentage > $highest) {
                        $highest = $percentage;
                    }
                    if ($percentage < $lowest) {
                        $lowest = $percentage;
                    }
                }
            }

            $avg = $appeared > 0 ? round($sumPercentage / $appeared, 2) : 0;
            $report[] = array(
                'month_name' => $monthData['month_name'],
                'class' => $monthData['class'],
                'exam_name' => $monthData['exam_name'],
                'appeared' => $appeared,
                'passed' => $passed,
                'failed' => $failed,
                'avg_marks' => $avg,
                'highest' => $highest,
                'lowest' => $lowest,
            );
        }

        return array('report' => $report);
    }

    public function give_monthly_test_reward($user, $post, $file)
    {
        $this->sync_legacy_session($user);

        $student_id = isset($post['student_id']) ? (int) $post['student_id'] : 0;
        $exam_id = isset($post['exam_id']) ? (int) $post['exam_id'] : 0;
        $month_key = isset($post['month_key']) ? $post['month_key'] : '';
        $reward_rule_id = isset($post['reward_rule_id']) ? (int) $post['reward_rule_id'] : 0;
        $improvement_count = isset($post['improvement_count']) ? (int) $post['improvement_count'] : 0;
        $remarks = isset($post['remarks']) ? $post['remarks'] : '';

        $st_detail = $this->ci->db->get_where('students', array('student_id' => $student_id))->row();
        if (!$st_detail) {
            return array('success' => false, 'message' => 'Student not found');
        }

        $reward_rule = $this->ci->db->where('id', $reward_rule_id)->get('monthly_test_reward_rules')->row_array();
        if (!$reward_rule) {
            return array('success' => false, 'message' => 'Reward rule not found');
        }

        $monthly_test = $this->ci->db->where('id', $reward_rule['exam_id'])->get('monthly_test_exams')->row_array();
        if (!$monthly_test) {
            return array('success' => false, 'message' => 'Exam not found');
        }

        $petty = $this->ci->db->get_where('petty_cash_college_wise', array('assign_to' => (int) $user['user_id']))->result_array();
        if (count($petty) === 0) {
            return array('success' => false, 'message' => "You don't have Petty Cash.");
        }

        $this->ci->load->helper('custom');
        $pettycash = my_pettycash();
        if ($pettycash < (float) $reward_rule['cash_amount']) {
            return array('success' => false, 'message' => 'Your Petty Cash is Less then reward Amount.');
        }

        $image = '';
        if (!empty($file['proof_image']) && !empty($file['proof_image']['tmp_name'])) {
            $config = array(
                'upload_path' => FCPATH . 'uploads/',
                'allowed_types' => 'gif|jpg|jpeg|png',
            );
            $this->ci->load->library('upload', $config);
            if ($this->ci->upload->do_upload('proof_image')) {
                $upload_data = $this->ci->upload->data();
                if (!empty($upload_data['file_name'])) {
                    $image = $upload_data['file_name'];
                }
            }
        }

        $exp_cat = json_decode($monthly_test['expense_category'], true);
        if (!is_array($exp_cat)) {
            $exp_cat = array();
        }
        $exp_id = !empty($exp_cat) ? end($exp_cat) : 0;

        $title = "Reward Given to Student {$st_detail->roll_no} for improvement number {$improvement_count} for the of month {$month_key}";

        $this->ci->db->set('campus_id', $st_detail->study_campus);
        $this->ci->db->set('expense_category_id', $exp_id);
        $this->ci->db->set('title', $title);
        $this->ci->db->set('date', date('Y-m-d'));
        $this->ci->db->set('amount', $reward_rule['cash_amount']);
        $this->ci->db->set('purpose', $title);
        $this->ci->db->set('month_year', date('Y-m'));
        $this->ci->db->set('student_id', $student_id);
        $this->ci->db->set('actual_date', date('Y-m-d H:i:s'));
        $this->ci->db->set('image', $image);
        $this->ci->db->set('payment_type', 'cash');
        $this->ci->db->set('class_id', $st_detail->class_id);
        $this->ci->db->set('roll_no', $st_detail->roll_no);
        $this->ci->db->set('add_by', trim($user['first_name'] . ' ' . $user['last_name']));
        $this->ci->db->set('last_edit', trim($user['first_name'] . ' ' . $user['last_name']));
        $this->ci->db->set('add_by_id', (int) $user['user_id']);
        $this->ci->db->set('approved_status', 1);
        $this->ci->db->set('paid_type', 'cash');
        $this->ci->db->insert('expenses');
        $expense_id = (int) $this->ci->db->insert_id();

        $this->ci->db->set('remaining_amount', 'remaining_amount - ' . (float) $reward_rule['cash_amount'], false);
        $this->ci->db->where('assign_to', (int) $user['user_id']);
        $this->ci->db->update('petty_cash_college_wise');

        $already = $this->ci->db
            ->where('student_id', $student_id)
            ->where('exam_id', $exam_id)
            ->where('month_key', $month_key)
            ->get('monthly_test_rewards_given')
            ->row_array();

        $reward_data = array(
            'student_id' => $student_id,
            'exam_id' => $exam_id,
            'month_key' => $month_key,
            'improvement_count' => $improvement_count,
            'reward_rule_id' => $reward_rule_id,
            'certificate' => $reward_rule['certificate'],
            'cash_amount' => $reward_rule['cash_amount'],
            'remarks' => $remarks,
            'given_by' => (int) $user['user_id'],
            'given_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'expense_id' => $expense_id,
        );

        if ($image !== '') {
            $reward_data['proof_image'] = $image;
        }

        if ($already) {
            $this->ci->db->where('id', $already['id'])->update('monthly_test_rewards_given', $reward_data);
        } else {
            $reward_data['created_at'] = date('Y-m-d H:i:s');
            $this->ci->db->insert('monthly_test_rewards_given', $reward_data);
        }

        return array('success' => true, 'message' => 'Reward given successfully');
    }

    private function build_improvement_report_rows($results)
    {
        $report = array();

        foreach ($results as $row) {
            $studentExamKey = $row['student_id'] . '_' . $row['exam_id'];
            $monthKey = date('Y-m', strtotime($row['date']));
            $monthName = date('F Y', strtotime($row['date']));

            if (!isset($report[$studentExamKey])) {
                $report[$studentExamKey] = array(
                    'student_id' => $row['student_id'],
                    'student' => trim($row['first_name'] . ' ' . $row['last_name']),
                    'class' => !empty($row['class_name']) ? $row['class_name'] : $row['paper_class'],
                    'exam_id' => $row['exam_id'],
                    'exam_name' => !empty($row['exam_name']) ? $row['exam_name'] : '-',
                    'month_attempts' => array(),
                    'attempts' => array(),
                    'improvement_count' => 0,
                    'reward' => 'Not Eligible',
                    'reward_text' => '',
                );
            }

            if (!isset($report[$studentExamKey]['month_attempts'][$monthKey])) {
                $report[$studentExamKey]['month_attempts'][$monthKey] = array(
                    'month_key' => $monthKey,
                    'month_name' => $monthName,
                    'obtain_marks' => 0,
                    'total_marks' => 0,
                    'percentage' => 0,
                    'papers_count' => 0,
                    'papers' => array(),
                    'is_improved' => 0,
                    'improvement_no' => 0,
                    'reward_rule' => null,
                    'reward_given' => 0,
                    'reward_given_data' => null,
                );
            }

            $obtain_marks = (float) $row['obtain_marks'];
            $total_marks = (float) $row['total_marks'];
            $report[$studentExamKey]['month_attempts'][$monthKey]['obtain_marks'] += $obtain_marks;
            $report[$studentExamKey]['month_attempts'][$monthKey]['total_marks'] += $total_marks;
            $report[$studentExamKey]['month_attempts'][$monthKey]['papers_count']++;
            $report[$studentExamKey]['month_attempts'][$monthKey]['papers'][] = array(
                'paper_id' => $row['collegepaper_id'],
                'date' => $row['date'],
                'obtain_marks' => $obtain_marks,
                'total_marks' => $total_marks,
            );
        }

        foreach ($report as $key => $studentReport) {
            ksort($report[$key]['month_attempts']);
            foreach ($report[$key]['month_attempts'] as $monthKey => $monthData) {
                $percentage = 0;
                if ($monthData['total_marks'] > 0) {
                    $percentage = round(($monthData['obtain_marks'] / $monthData['total_marks']) * 100, 2);
                }
                $monthData['percentage'] = $percentage;
                $report[$key]['attempts'][] = $monthData;
            }
        }

        foreach ($report as $key => $studentReport) {
            $attempts = $studentReport['attempts'];
            $improvement_count = 0;

            if (isset($report[$key]['attempts'][0])) {
                $report[$key]['attempts'][0]['is_improved'] = 0;
                $report[$key]['attempts'][0]['improvement_no'] = 0;
                $report[$key]['attempts'][0]['reward_rule'] = null;
                $report[$key]['attempts'][0]['reward_given'] = 0;
                $report[$key]['attempts'][0]['reward_given_data'] = null;
            }

            for ($i = 1; $i < count($attempts); $i++) {
                $report[$key]['attempts'][$i]['is_improved'] = 0;
                $report[$key]['attempts'][$i]['improvement_no'] = 0;
                $report[$key]['attempts'][$i]['reward_rule'] = null;
                $report[$key]['attempts'][$i]['reward_given'] = 0;
                $report[$key]['attempts'][$i]['reward_given_data'] = null;

                if ($attempts[$i]['percentage'] > $attempts[$i - 1]['percentage']) {
                    $improvement_count++;
                    $improvement_no = $improvement_count;

                    $reward_rule = $this->ci->db
                        ->where('exam_id', $studentReport['exam_id'])
                        ->where('improvement_count', $improvement_no)
                        ->where('status', 1)
                        ->get('monthly_test_reward_rules')
                        ->row_array();

                    $given_reward = array();
                    if ($reward_rule) {
                        $given_reward = $this->ci->db
                            ->where('student_id', $studentReport['student_id'])
                            ->where('exam_id', $studentReport['exam_id'])
                            ->where('month_key', $attempts[$i]['month_key'])
                            ->where('improvement_no', $improvement_no)
                            ->get('monthly_test_rewards_given')
                            ->row_array();
                    }

                    $report[$key]['attempts'][$i]['is_improved'] = 1;
                    $report[$key]['attempts'][$i]['improvement_no'] = $improvement_no;
                    $report[$key]['attempts'][$i]['reward_rule'] = $reward_rule ?: null;
                    $report[$key]['attempts'][$i]['reward_given'] = !empty($given_reward) ? 1 : 0;
                    $report[$key]['attempts'][$i]['reward_given_data'] = $given_reward ?: null;
                }
            }

            $report[$key]['improvement_count'] = $improvement_count;

            $lastRewardRule = $this->ci->db
                ->where('exam_id', $studentReport['exam_id'])
                ->where('improvement_count <=', $improvement_count)
                ->where('status', 1)
                ->order_by('improvement_count', 'DESC')
                ->get('monthly_test_reward_rules')
                ->row_array();

            if ($lastRewardRule) {
                $report[$key]['reward'] = 'Eligible';
                $rewardText = array();
                if ($lastRewardRule['certificate'] == 1) {
                    $rewardText[] = 'Certificate';
                }
                if ($lastRewardRule['cash_amount'] > 0) {
                    $rewardText[] = 'Cash: ' . $lastRewardRule['cash_amount'];
                }
                $report[$key]['reward_text'] = !empty($rewardText) ? implode(' + ', $rewardText) : '-';
            }
        }

        return array_values($report);
    }

    private function grade_for_percentage($pct)
    {
        if ($pct >= 80) {
            return 'A';
        }
        if ($pct >= 70) {
            return 'B';
        }
        if ($pct >= 60) {
            return 'C';
        }
        if ($pct >= 50) {
            return 'D';
        }
        return 'F';
    }

    private function normalize_id_list($value)
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('intval', $value)));
        }
        if ($value === null || $value === '') {
            return array();
        }
        return array_values(array_filter(array_map('intval', explode(',', (string) $value))));
    }

    private function sync_legacy_session($user)
    {
        if (!$user) {
            return;
        }
        $this->ci->session->set_userdata(array(
            'user_id' => (int) $user['user_id'],
            'name' => trim($user['first_name'] . ' ' . $user['last_name']),
            'username' => isset($user['username']) ? $user['username'] : '',
            'designation_id' => isset($user['designation_id']) ? $user['designation_id'] : null,
            'role' => isset($user['role']) ? $user['role'] : '',
            'cnic' => isset($user['cnic']) ? $user['cnic'] : '',
            'type' => isset($user['type']) ? $user['type'] : '',
            'user_campus_id' => isset($user['campus_id']) ? $user['campus_id'] : null,
            'logged_in' => true,
        ));
    }
}
