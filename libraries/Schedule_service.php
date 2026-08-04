<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Schedule_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function can_access($user)
    {
        if (!$user) return false;
        if ($user['role'] === 'Admin') return true;
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        if (!$acc) return false;
        return !empty($acc['schedule_management_sidebar']) || !empty($acc['timetable_sidebar']);
    }

    public function permissions($user)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : ($this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array() ?: array());
        return array(
            'is_admin' => $is_admin,
            'schedule_management' => $is_admin || !empty($acc['schedule_management_sidebar']) || !empty($acc['timetable_sidebar']),
            'syllabus' => $is_admin || !empty($acc['syllabus_sidebar']),
            'make_syllabus' => $is_admin || !empty($acc['make_lecture']),
            'all_syllabus' => $is_admin || !empty($acc['all_lecture']),
            'session_syllabus' => $is_admin || !empty($acc['session_wise_syllabus']),
            'timetable' => $is_admin || !empty($acc['timetable_sidebar']),
            'study_type' => $is_admin || !empty($acc['study_type']),
            'shifts' => $is_admin || !empty($acc['shifts']),
            'add_timetable' => $is_admin || !empty($acc['add_timetable']),
            'view_timetable' => $is_admin || !empty($acc['view_timetable']),
        );
    }

    public function meta($user)
    {
        $courses = $this->ci->db->order_by('course_name', 'ASC')->get('courses')->result_array();
        $campuses = $this->ci->db->order_by('campus_name', 'ASC')->get_where('campuses', array('status' => 1))->result_array();
        $shifts = $this->ci->db->order_by('name', 'ASC')->get('shifts')->result_array();
        $teachers = $this->ci->db
            ->select('users.user_id, users.first_name, users.last_name, campuses.campus_name')
            ->from('users')
            ->join('campuses', 'campuses.campus_id = users.campus_id', 'left')
            ->where('users.status', '1')
            ->where('users.department_id', '13')
            ->order_by('users.first_name', 'ASC')
            ->get()->result_array();
        return array(
            'permissions' => $this->permissions($user),
            'courses' => $courses,
            'campuses' => $campuses,
            'shifts' => $shifts,
            'teachers' => $teachers,
            'week_days' => array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
        );
    }

    public function subjects($course_id)
    {
        $course_id = (int) $course_id;
        if ($course_id <= 0) return array();
        return $this->ci->db
            ->select('course_subject_id, subject_name')
            ->from('course_subjects')
            ->where('course_id', $course_id)
            ->order_by('subject_name', 'ASC')
            ->get()->result_array();
    }

    public function study_types($course_id)
    {
        $course_id = (int) $course_id;
        if ($course_id <= 0) return array();
        return $this->ci->db
            ->select('id, name')
            ->from('study_type')
            ->where('course_id', $course_id)
            ->order_by('name', 'ASC')
            ->get()->result_array();
    }

    public function syllabus_content($subject_id)
    {
        $subject_id = (int) $subject_id;
        if ($subject_id <= 0) return array('chapters' => array(), 'practicals' => array());

        $topics = $this->ci->db
            ->select('topics.topic_id, topics.topic_name, topics.chapter_id, chapters.chapter_name')
            ->from('topics')
            ->join('chapters', 'chapters.chapter_id = topics.chapter_id', 'inner')
            ->where('topics.course_subject_id', $subject_id)
            ->order_by('topics.chapter_id', 'ASC')
            ->order_by('topics.topic_id', 'ASC')
            ->get()->result_array();

        $chapters = array();
        foreach ($topics as $topic) {
            $cid = (int) $topic['chapter_id'];
            if (!isset($chapters[$cid])) {
                $chapters[$cid] = array(
                    'chapter_id' => $cid,
                    'chapter_name' => $topic['chapter_name'],
                    'topics' => array(),
                );
            }
            $chapters[$cid]['topics'][] = array(
                'topic_id' => (int) $topic['topic_id'],
                'topic_name' => $topic['topic_name'],
            );
        }

        $practicals = $this->ci->db
            ->select('practical_id, practical_name')
            ->from('practicals')
            ->where('subject_id', $subject_id)
            ->order_by('practical_id', 'ASC')
            ->get()->result_array();

        return array(
            'chapters' => array_values($chapters),
            'practicals' => $practicals,
        );
    }

    public function validate_plan($items)
    {
        $parsed = array();
        $warnings = array();
        $slots = array();
        $max_lecture = 0;

        if (!is_array($items) || !count($items)) {
            return array(
                'valid' => false,
                'warnings' => array('Add at least one topic or practical assignment.'),
                'timeline' => array(),
                'max_lecture' => 0,
                'items' => array(),
            );
        }

        foreach ($items as $idx => $item) {
            $label = isset($item['label']) ? $item['label'] : ('Item '.($idx + 1));
            $kind = isset($item['kind']) ? $item['kind'] : 'topic';
            $built = $this->_build_require_lectures($item);
            if (!$built['valid']) {
                $warnings[] = $label.': '.$built['message'];
                continue;
            }

            $numbers = $built['numbers'];
            foreach ($numbers as $num) {
                if (!isset($slots[$num])) {
                    $slots[$num] = array('topics' => array(), 'practicals' => array());
                }
                $list_key = ($kind === 'practical') ? 'practicals' : 'topics';
                if (in_array($label, $slots[$num][$list_key], true)) {
                    // same item listed twice in range — ok
                } elseif (count($slots[$num][$list_key]) > 0) {
                    $warnings[] = 'Lecture '.$num.' has multiple '.($kind === 'practical' ? 'practicals' : 'topics').': "'.$slots[$num][$list_key][0].'" and "'.$label.'".';
                    $slots[$num][$list_key][] = $label;
                } else {
                    $slots[$num][$list_key][] = $label;
                }
                if ($num > $max_lecture) $max_lecture = $num;
            }

            $parsed[] = array(
                'kind' => $kind,
                'topic_id' => isset($item['topic_id']) ? (int) $item['topic_id'] : null,
                'practical_id' => isset($item['practical_id']) ? (int) $item['practical_id'] : null,
                'label' => $label,
                'require_lectures' => $built['require_lectures'],
                'lecture_numbers' => $numbers,
            );
        }

        if ($max_lecture > 0) {
            for ($i = 1; $i <= $max_lecture; $i++) {
                if (!isset($slots[$i]) || (count($slots[$i]['topics']) === 0 && count($slots[$i]['practicals']) === 0)) {
                    $warnings[] = 'Lecture '.$i.' has no topic or practical assigned (gap in sequence).';
                }
            }
        }

        $timeline = array();
        for ($i = 1; $i <= $max_lecture; $i++) {
            $slot = isset($slots[$i]) ? $slots[$i] : array('topics' => array(), 'practicals' => array());
            $parts = array_merge($slot['topics'], $slot['practicals']);
            $timeline[] = array(
                'lecture' => $i,
                'label' => count($parts) ? implode(' + ', $parts) : null,
                'topics' => $slot['topics'],
                'practicals' => $slot['practicals'],
            );
        }

        return array(
            'valid' => count($warnings) === 0 && count($parsed) === count($items),
            'warnings' => $warnings,
            'timeline' => $timeline,
            'max_lecture' => $max_lecture,
            'items' => $parsed,
        );
    }

    public function save_syllabus($user, $payload)
    {
        if (!$this->permissions($user)['make_syllabus']) {
            return array('success' => false, 'message' => 'Make syllabus permission required');
        }

        $course_id = isset($payload['course_id']) ? (int) $payload['course_id'] : 0;
        $subject_id = isset($payload['subject_id']) ? (int) $payload['subject_id'] : 0;
        $studytype = isset($payload['studytype']) ? (int) $payload['studytype'] : 0;
        $revision = isset($payload['revision']) ? (int) $payload['revision'] : 0;
        $syllabus_name = isset($payload['syllabus_name']) ? trim($payload['syllabus_name']) : '';
        $items = isset($payload['items']) ? $payload['items'] : array();

        if ($course_id <= 0 || $subject_id <= 0 || $studytype <= 0) {
            return array('success' => false, 'message' => 'Course, subject, and study type are required');
        }
        if ($syllabus_name === '') {
            return array('success' => false, 'message' => 'Syllabus name is required');
        }

        $validation = $this->validate_plan($items);
        if (!$validation['valid']) {
            $msg = count($validation['warnings']) ? $validation['warnings'][0] : 'Invalid syllabus plan';
            return array('success' => false, 'message' => $msg, 'warnings' => $validation['warnings']);
        }

        $max_row = $this->ci->db->select_max('unique_syllabus_id')->get('syllabus')->row_array();
        $unique_syllabus_id = ($max_row && !empty($max_row['unique_syllabus_id'])) ? ((int) $max_row['unique_syllabus_id'] + 1) : 1;

        $name = trim((isset($user['first_name']) ? $user['first_name'] : '').' '.(isset($user['last_name']) ? $user['last_name'] : ''));
        if ($name === '') $name = 'Admin';

        foreach ($validation['items'] as $row) {
            $insert = array(
                'unique_syllabus_id' => $unique_syllabus_id,
                'syllabus_name' => $syllabus_name,
                'course_id' => $course_id,
                'subject_id' => $subject_id,
                'require_lectures' => $row['require_lectures'],
                'revision' => $revision,
                'studytype' => $studytype,
                'add_by' => $name,
                'last_edit' => $name,
            );
            if ($row['kind'] === 'practical') {
                $insert['practical_id'] = $row['practical_id'];
                $insert['topic_id'] = null;
            } else {
                $insert['topic_id'] = $row['topic_id'];
                $insert['practical_id'] = null;
            }
            $this->ci->db->insert('syllabus', $insert);
        }

        return array(
            'success' => true,
            'message' => 'Syllabus plan saved',
            'unique_syllabus_id' => $unique_syllabus_id,
        );
    }

    public function syllabus_plans($filters = array())
    {
        $this->ci->db->select('syllabus.*, courses.course_name, course_subjects.subject_name, study_type.name AS study_type_name');
        $this->ci->db->from('syllabus');
        $this->ci->db->join('courses', 'courses.course_id=syllabus.course_id', 'inner');
        $this->ci->db->join('study_type', 'study_type.id=syllabus.studytype', 'inner');
        $this->ci->db->join('course_subjects', 'course_subjects.course_subject_id=syllabus.subject_id', 'inner');
        $this->ci->db->where('syllabus.practical_id IS NULL', null, false);
        if (!empty($filters['course_id'])) $this->ci->db->where('syllabus.course_id', (int) $filters['course_id']);
        if (!empty($filters['subject_id'])) $this->ci->db->where('syllabus.subject_id', (int) $filters['subject_id']);
        if (!empty($filters['studytype'])) $this->ci->db->where('syllabus.studytype', (int) $filters['studytype']);
        $this->ci->db->where('(syllabus.require_lectures LIKE "1" OR syllabus.require_lectures LIKE "1-%")', null, false);
        $this->ci->db->order_by('syllabus.syllabus_id', 'ASC');
        $this->ci->db->group_by('syllabus.unique_syllabus_id');
        $rows = $this->ci->db->get()->result_array();
        $out = array();
        foreach ($rows as $row) {
            $out[] = array(
                'unique_syllabus_id' => (int) $row['unique_syllabus_id'],
                'syllabus_name' => $row['syllabus_name'],
                'course_id' => (int) $row['course_id'],
                'course_name' => $row['course_name'],
                'subject_id' => (int) $row['subject_id'],
                'subject_name' => $row['subject_name'],
                'study_type' => $row['study_type_name'],
                'revision' => (int) $row['revision'],
                'revision_label' => ((int) $row['revision'] === 0) ? 'Regular' : 'Revision',
                'add_by' => isset($row['add_by']) ? $row['add_by'] : '',
            );
        }
        return $out;
    }

    public function syllabus_plan_detail($unique_syllabus_id)
    {
        $unique_syllabus_id = (int) $unique_syllabus_id;
        if ($unique_syllabus_id <= 0) return null;
        $rows = $this->ci->db->get_where('syllabus', array('unique_syllabus_id' => $unique_syllabus_id))->result_array();
        if (!count($rows)) return null;

        $head = $rows[0];
        $subject = $this->ci->db->get_where('course_subjects', array('course_subject_id' => $head['subject_id']))->row_array();
        $items = array();
        foreach ($rows as $row) {
            $label = '';
            if (!empty($row['topic_id'])) {
                $topic = $this->ci->db->get_where('topics', array('topic_id' => $row['topic_id']))->row_array();
                $label = $topic ? $topic['topic_name'] : 'Topic #'.$row['topic_id'];
            } elseif (!empty($row['practical_id'])) {
                $pr = $this->ci->db->get_where('practicals', array('practical_id' => $row['practical_id']))->row_array();
                $label = $pr ? $pr['practical_name'] : 'Practical #'.$row['practical_id'];
            }
            $items[] = array(
                'kind' => !empty($row['practical_id']) ? 'practical' : 'topic',
                'label' => $label,
                'require_lectures' => $row['require_lectures'],
            );
        }

        return array(
            'unique_syllabus_id' => $unique_syllabus_id,
            'syllabus_name' => $head['syllabus_name'],
            'subject_id' => (int) $head['subject_id'],
            'subject_name' => $subject ? $subject['subject_name'] : '',
            'revision' => (int) $head['revision'],
            'items' => $items,
        );
    }

    public function delete_syllabus_plan($unique_syllabus_id)
    {
        $unique_syllabus_id = (int) $unique_syllabus_id;
        if ($unique_syllabus_id <= 0) return array('success' => false, 'message' => 'Invalid plan');
        $this->ci->db->where('unique_syllabus_id', $unique_syllabus_id);
        $this->ci->db->delete('syllabus');
        return array('success' => true, 'message' => 'Syllabus plan deleted');
    }

    public function session_syllabus_list($filters = array())
    {
        $this->ci->db->select('session_syllabus.*, courses.course_name, course_subjects.subject_name, lectures.lecture_name, study_type.name AS study_type_name, syllabus.revision, shifts.name AS shift_name, campuses.campus_name');
        $this->ci->db->from('session_syllabus');
        $this->ci->db->join('course_subjects', 'course_subjects.course_subject_id=session_syllabus.subject_id', 'inner');
        $this->ci->db->join('courses', 'courses.course_id=course_subjects.course_id', 'inner');
        $this->ci->db->join('lectures', 'lectures.id=session_syllabus.lecture_id', 'inner');
        $this->ci->db->join('campuses', 'lectures.campus=campuses.campus_id', 'inner');
        $this->ci->db->join('syllabus', 'syllabus.unique_syllabus_id=session_syllabus.syllabus_id', 'inner');
        $this->ci->db->join('study_type', 'syllabus.studytype=study_type.id', 'inner');
        $this->ci->db->join('shifts', 'shifts.id=lectures.shift', 'inner');
        if (!empty($filters['course_id'])) $this->ci->db->where('courses.course_id', (int) $filters['course_id']);
        if (!empty($filters['shift_id'])) $this->ci->db->where('shifts.id', (int) $filters['shift_id']);
        if (!empty($filters['campus_id'])) $this->ci->db->where('campuses.campus_id', (int) $filters['campus_id']);
        $this->ci->db->group_by('session_syllabus.subject_id');
        $this->ci->db->group_by('syllabus.unique_syllabus_id');
        $rows = $this->ci->db->get()->result_array();

        $out = array();
        foreach ($rows as $row) {
            $dates = $this->ci->db
                ->select('date')
                ->from('session_syllabus')
                ->where(array('lecture_id' => $row['lecture_id'], 'subject_id' => $row['subject_id']))
                ->order_by('date', 'ASC')
                ->get()->result_array();
            $start_date = count($dates) ? $dates[0]['date'] : '';
            $end_date = count($dates) ? $dates[count($dates) - 1]['date'] : '';

            $out[] = array(
                'subject_id' => (int) $row['subject_id'],
                'lecture_id' => (int) $row['lecture_id'],
                'syllabus_id' => (int) $row['syllabus_id'],
                'course_name' => $row['course_name'],
                'campus_name' => $row['campus_name'],
                'subject_name' => $row['subject_name'],
                'lecture_name' => $row['lecture_name'],
                'sessions' => $row['sessions'],
                'shift_name' => $row['shift_name'],
                'study_type' => $row['study_type_name'],
                'revision_label' => ((int) $row['revision'] === 0) ? 'Regular' : 'Revision',
                'start_date' => $start_date,
                'end_date' => $end_date,
            );
        }
        return $out;
    }

    public function session_syllabus_detail($subject_id, $lecture_id, $merged = false)
    {
        $subject_id = (int) $subject_id;
        $lecture_id = (int) $lecture_id;
        $this->ci->db->select('session_syllabus.*, course_subjects.subject_name');
        $this->ci->db->from('session_syllabus');
        $this->ci->db->join('course_subjects', 'course_subjects.course_subject_id=session_syllabus.subject_id', 'inner');
        if (!$merged) {
            $this->ci->db->where('session_syllabus.subject_id', $subject_id);
        }
        $this->ci->db->where('session_syllabus.lecture_id', $lecture_id);
        if ($merged) $this->ci->db->group_by('session_syllabus.date');
        $this->ci->db->order_by('session_syllabus.date', 'ASC');
        $rows = $this->ci->db->get()->result_array();

        $out = array();
        $row_count = count($rows);
        foreach ($rows as $idx => $row) {
            $topics = array();
            $practicals = array();
            if (!empty($row['topic_ids'])) {
                $ids = array_filter(array_map('intval', explode(',', rtrim($row['topic_ids'], ','))));
                if (count($ids)) {
                    $topics = $this->ci->db->where_in('topic_id', $ids)->get('topics')->result_array();
                }
            }
            if (!empty($row['practical_ids'])) {
                $ids = array_filter(array_map('intval', explode(',', rtrim($row['practical_ids'], ','))));
                if (count($ids)) {
                    $practicals = $this->ci->db->where_in('practical_id', $ids)->get('practicals')->result_array();
                }
            }
            $is_quiz = (int) $row['is_quiz'];
            $is_half = (int) $row['is_half'];
            $is_full = ($is_quiz === 1 && $is_half === 0 && $idx === $row_count - 1);
            $out[] = array(
                'id' => (int) $row['id'],
                'subject_id' => (int) $row['subject_id'],
                'subject_name' => $row['subject_name'],
                'day' => $row['day'],
                'date' => $row['date'],
                'is_quiz' => $is_quiz,
                'is_half' => $is_half,
                'is_full' => $is_full ? 1 : 0,
                'topics' => array_map(function ($t) { return $t['topic_name']; }, $topics),
                'practicals' => array_map(function ($p) { return $p['practical_name']; }, $practicals),
            );
        }
        return $out;
    }

    public function delete_session_syllabus($subject_id, $lecture_id, $syllabus_id)
    {
        $this->ci->db->where('subject_id', (int) $subject_id);
        $this->ci->db->where('lecture_id', (int) $lecture_id);
        $this->ci->db->where('syllabus_id', (int) $syllabus_id);
        $this->ci->db->delete('session_syllabus');
        return array('success' => true, 'message' => 'Generated syllabus deleted');
    }

    public function timetable_lectures($campus_id = null)
    {
        $this->ci->db->select('lectures.*, courses.course_name, campuses.campus_name, users.first_name, users.last_name, rooms.room_name');
        $this->ci->db->from('lectures');
        $this->ci->db->join('courses', 'courses.course_id=lectures.course', 'left');
        $this->ci->db->join('campuses', 'campuses.campus_id=lectures.campus', 'left');
        $this->ci->db->join('users', 'users.user_id=lectures.teacher', 'left');
        $this->ci->db->join('rooms', 'rooms.room_id=lectures.room', 'left');
        if ($campus_id !== null && $campus_id !== '' && $campus_id !== 'all') {
            $this->ci->db->where('lectures.campus', (int) $campus_id);
        }
        $this->ci->db->order_by('lectures.id', 'DESC');
        $rows = $this->ci->db->get()->result_array();

        $out = array();
        foreach ($rows as $row) {
            $subject_ids = array_filter(array_map('intval', explode(',', $row['subjects'])));
            $subjects = array();
            if (count($subject_ids)) {
                $subjects = $this->ci->db->where_in('course_subject_id', $subject_ids)->get('course_subjects')->result_array();
            }
            $shift = $this->ci->db->get_where('shifts', array('id' => $row['shift']))->row_array();
            $study = $this->ci->db->get_where('study_type', array('id' => $row['studytype']))->row_array();
            $subject_actions = array();
            foreach ($subjects as $sub) {
                $sid = (int) $sub['course_subject_id'];
                $has_generated = $this->ci->db
                    ->where(array('lecture_id' => $row['id'], 'subject_id' => $sid))
                    ->count_all_results('session_syllabus') > 0;
                $subject_actions[] = array(
                    'subject_id' => $sid,
                    'subject_name' => $sub['subject_name'],
                    'has_generated' => $has_generated,
                );
            }
            $out[] = array(
                'id' => (int) $row['id'],
                'lecture_name' => $row['lecture_name'],
                'campus' => (int) $row['campus'],
                'campus_name' => $row['campus_name'],
                'course_name' => $row['course_name'],
                'class_year' => $row['class'],
                'session' => $row['session'],
                'subjects' => array_map(function ($s) { return $s['subject_name']; }, $subjects),
                'subject_actions' => $subject_actions,
                'shift' => (int) $row['shift'],
                'shift_name' => $shift ? $shift['name'] : '',
                'studytype' => (int) $row['studytype'],
                'study_type_name' => $study ? $study['name'] : '',
                'days' => array_filter(array_map('trim', explode(',', $row['days']))),
                'room_name' => $row['room_name'],
                'teacher_name' => trim($row['first_name'].' '.$row['last_name']),
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'zoom_id' => isset($row['zoom_id']) ? $row['zoom_id'] : '',
                'zoom_password' => isset($row['zoom_password']) ? $row['zoom_password'] : '',
                'pending_lectures' => $this->_pending_lectures_count((int) $row['id']),
                'has_any_generated' => count(array_filter($subject_actions, function ($s) { return $s['has_generated']; })) > 0,
            );
        }
        return $out;
    }

    public function available_syllabuses($subject_id, $studytype)
    {
        $this->ci->db->select('syllabus.*');
        $this->ci->db->from('syllabus');
        $this->ci->db->where('syllabus.practical_id IS NULL', null, false);
        $this->ci->db->where('syllabus.subject_id', (int) $subject_id);
        $this->ci->db->where('syllabus.studytype', (int) $studytype);
        $this->ci->db->where('(syllabus.require_lectures LIKE "1" OR syllabus.require_lectures LIKE "1-%")', null, false);
        $this->ci->db->group_by('syllabus.unique_syllabus_id');
        $this->ci->db->order_by('syllabus.syllabus_id', 'ASC');
        $rows = $this->ci->db->get()->result_array();

        $out = array();
        $revision_count = 1;
        foreach ($rows as $row) {
            $used = $this->ci->db->where('syllabus_id', $row['unique_syllabus_id'])->count_all_results('session_syllabus');
            if ($used > 0) continue;
            $label = ((int) $row['revision'] === 0)
                ? 'Regular - '.$row['syllabus_name']
                : 'Revision '.$revision_count.' - '.$row['syllabus_name'];
            if ((int) $row['revision'] !== 0) $revision_count++;
            $out[] = array(
                'unique_syllabus_id' => (int) $row['unique_syllabus_id'],
                'label' => $label,
                'syllabus_name' => $row['syllabus_name'],
                'revision' => (int) $row['revision'],
            );
        }
        return $out;
    }

    public function suggested_start_date($subject_id, $lecture_id)
    {
        $row = $this->ci->db
            ->where(array('subject_id' => (int) $subject_id, 'lecture_id' => (int) $lecture_id))
            ->order_by('date', 'DESC')
            ->limit(1)
            ->get('session_syllabus')->row_array();
        if (!$row) return '';
        return date('Y-m-d', strtotime('+5 day', strtotime($row['date'])));
    }

    public function generate_session_preview($lecture_id, $subject_id, $unique_syllabus_id, $start_date, $test_after)
    {
        $lecture = $this->ci->db->get_where('lectures', array('id' => (int) $lecture_id))->row_array();
        if (!$lecture) return array('success' => false, 'message' => 'Lecture not found');

        $subject = $this->ci->db->get_where('course_subjects', array('course_subject_id' => (int) $subject_id))->row_array();
        if (!$subject) return array('success' => false, 'message' => 'Subject not found');

        $max_lectures = $this->_syllabus_max_lecture((int) $unique_syllabus_id);
        if ($max_lectures <= 0) return array('success' => false, 'message' => 'Syllabus plan has no lectures');

        $test_after = (int) $test_after;
        if ($test_after < 1) $test_after = 3;

        $quiz_slots = $this->_count_quiz_slots_for_lectures($max_lectures, $test_after);
        $content_and_quiz_slots = $max_lectures + $quiz_slots;
        $date_slots = $this->_build_lecture_dates($lecture, $start_date, $content_and_quiz_slots + 1);
        $template_rows = $this->ci->db->get_where('syllabus', array('unique_syllabus_id' => (int) $unique_syllabus_id))->result_array();

        $half_quiz_index = (int) floor($quiz_slots / 2);

        $quiz_topic_ids = array();
        $quiz_practical_ids = array();
        $quiz_topic_names = array();
        $quiz_practical_names = array();
        $half_quiz_topic_ids = array();
        $half_quiz_practical_ids = array();
        $half_quiz_topic_names = array();
        $half_quiz_practical_names = array();

        $rows = array();
        $i = 0;
        $t = 1;
        $quiz_counts = 0;
        foreach (array_slice($date_slots, 0, $content_and_quiz_slots) as $idx => $slot) {
            if ($t > $test_after) {
                $is_half = ($quiz_counts === $half_quiz_index);
                if ($is_half) {
                    $q_topics = $half_quiz_topic_names;
                    $q_practicals = $half_quiz_practical_names;
                    $q_topic_ids = implode(',', $half_quiz_topic_ids);
                    $q_practical_ids = implode(',', $half_quiz_practical_ids);
                } else {
                    $q_topics = $quiz_topic_names;
                    $q_practicals = $quiz_practical_names;
                    $q_topic_ids = implode(',', $quiz_topic_ids);
                    $q_practical_ids = implode(',', $quiz_practical_ids);
                }

                $rows[] = array(
                    'sr' => $idx + 1,
                    'subject_name' => strtoupper($subject['subject_name']),
                    'day' => $slot['day'],
                    'date' => $slot['date'],
                    'is_quiz' => true,
                    'is_half' => $is_half,
                    'is_full' => false,
                    'topics' => $q_topics,
                    'practicals' => $q_practicals,
                    'topic_ids' => $q_topic_ids,
                    'practical_ids' => $q_practical_ids,
                );

                $quiz_topic_ids = array();
                $quiz_practical_ids = array();
                $quiz_topic_names = array();
                $quiz_practical_names = array();
                $quiz_counts++;
                $t = 1;
                continue;
            }

            $mapped = $this->_map_template_for_lecture($template_rows, $i + 1);
            $this->_accumulate_quiz_slot($quiz_topic_ids, $quiz_topic_names, $mapped['topic_ids'], $mapped['topic_names']);
            $this->_accumulate_quiz_slot($quiz_practical_ids, $quiz_practical_names, $mapped['practical_ids'], $mapped['practical_names']);
            $this->_accumulate_quiz_slot($half_quiz_topic_ids, $half_quiz_topic_names, $mapped['topic_ids'], $mapped['topic_names']);
            $this->_accumulate_quiz_slot($half_quiz_practical_ids, $half_quiz_practical_names, $mapped['practical_ids'], $mapped['practical_names']);

            $rows[] = array(
                'sr' => $idx + 1,
                'subject_name' => strtoupper($subject['subject_name']),
                'day' => $slot['day'],
                'date' => $slot['date'],
                'is_quiz' => false,
                'is_half' => false,
                'is_full' => false,
                'topics' => $mapped['topic_names'],
                'practicals' => $mapped['practical_names'],
                'topic_ids' => $mapped['topic_ids'],
                'practical_ids' => $mapped['practical_ids'],
            );
            $i++;
            $t++;
        }

        $full_slot = $date_slots[$content_and_quiz_slots];
        $rows[] = array(
            'sr' => count($rows) + 1,
            'subject_name' => strtoupper($subject['subject_name']),
            'day' => $full_slot['day'],
            'date' => $full_slot['date'],
            'is_quiz' => true,
            'is_half' => false,
            'is_full' => true,
            'topics' => $half_quiz_topic_names,
            'practicals' => $half_quiz_practical_names,
            'topic_ids' => implode(',', $half_quiz_topic_ids),
            'practical_ids' => implode(',', $half_quiz_practical_ids),
        );

        return array(
            'success' => true,
            'lecture_id' => (int) $lecture_id,
            'subject_id' => (int) $subject_id,
            'unique_syllabus_id' => (int) $unique_syllabus_id,
            'start_date' => $start_date,
            'sessions' => $lecture['session'],
            'content_lectures' => $max_lectures,
            'quiz_sessions' => $quiz_slots,
            'full_book_sessions' => 1,
            'total_sessions' => count($rows),
            'rows' => $rows,
        );
    }

    public function save_session_syllabus($user, $payload)
    {
        $sessions = isset($payload['sessions']) ? $payload['sessions'] : '';
        $subject_id = isset($payload['subject_id']) ? (int) $payload['subject_id'] : 0;
        $start_date = isset($payload['start_date']) ? $payload['start_date'] : '';
        $lecture_id = isset($payload['lecture_id']) ? (int) $payload['lecture_id'] : 0;
        $unique_syllabus_id = isset($payload['unique_syllabus_id']) ? (int) $payload['unique_syllabus_id'] : 0;
        $rows = isset($payload['rows']) ? $payload['rows'] : array();

        if (!$subject_id || !$lecture_id || !$unique_syllabus_id || !count($rows)) {
            return array('success' => false, 'message' => 'Missing required generation data');
        }

        $name = trim((isset($user['first_name']) ? $user['first_name'] : '').' '.(isset($user['last_name']) ? $user['last_name'] : ''));
        if ($name === '') $name = 'Admin';

        $fcount = 0;
        foreach ($rows as $row) {
            if (!empty($row['is_full'])) continue;
            if (!empty($row['is_quiz'])) {
                $fcount++;
                continue;
            }
            $pr = isset($row['practical_ids']) ? $row['practical_ids'] : '';
            $tp = isset($row['topic_ids']) ? $row['topic_ids'] : '';
            if (($pr === '' || $pr === '0') && $tp === '') $fcount++;
        }
        $fcount = (int) floor($fcount / 2);

        $quiz_counts = 0;
        $quiztopics = '';
        $quizpracticals = '';
        $half_quiz_topics = '';
        $half_quiz_practicals = '';

        foreach ($rows as $row) {
            $pr = isset($row['practical_ids']) ? $row['practical_ids'] : '';
            $tp = isset($row['topic_ids']) ? $row['topic_ids'] : '';
            $is_quiz = !empty($row['is_quiz']);
            $is_full = !empty($row['is_full']);

            if ($is_full) {
                $tops = $tp;
                $pracs = $pr;
                $this->ci->db->set('is_quiz', '1');
                $this->ci->db->set('is_half', '0');
            } elseif ($is_quiz || (($pr === '' || $pr === '0') && $tp === '')) {
                if ($quiz_counts == $fcount) {
                    $tops = $half_quiz_topics;
                    $pracs = $half_quiz_practicals;
                    $this->ci->db->set('is_quiz', '1');
                    $this->ci->db->set('is_half', '1');
                    $quizpracticals = '';
                    $quiztopics = '';
                } else {
                    $tops = $quiztopics;
                    $pracs = $quizpracticals;
                    $this->ci->db->set('is_quiz', '1');
                    $quizpracticals = '';
                    $quiztopics = '';
                }
                $quiz_counts++;
            } else {
                if ($tp !== '' && $tp !== null) {
                    $quiztopics .= $tp.',';
                    $half_quiz_topics .= $tp.',';
                }
                if ($pr !== '' && $pr !== null) {
                    $quizpracticals .= $pr.',';
                    $half_quiz_practicals .= $pr.',';
                }
                $tops = $tp;
                $pracs = $pr;
                $this->ci->db->set('is_quiz', '0');
                $this->ci->db->set('is_half', '0');
            }

            if ($tops === '' && $pracs === '') continue;

            $this->ci->db->set('sessions', $sessions);
            $this->ci->db->set('subject_id', $subject_id);
            $this->ci->db->set('day', $row['day']);
            $this->ci->db->set('date', $row['date']);
            $this->ci->db->set('topic_ids', $tops);
            $this->ci->db->set('practical_ids', $pracs);
            $this->ci->db->set('start_date', $start_date);
            $this->ci->db->set('lecture_id', $lecture_id);
            $this->ci->db->set('syllabus_id', $unique_syllabus_id);
            $this->ci->db->set('created_by', $name);
            $this->ci->db->insert('session_syllabus');
        }

        return array('success' => true, 'message' => 'Generated syllabus saved');
    }

    private function _parse_require_lecture_numbers($require_lectures)
    {
        $parts = explode('-', (string) $require_lectures);
        $nums = array();
        foreach ($parts as $part) {
            $n = (int) trim($part);
            if ($n > 0) $nums[] = $n;
        }
        return $nums;
    }

    private function _syllabus_max_lecture($unique_syllabus_id)
    {
        $rows = $this->ci->db->get_where('syllabus', array('unique_syllabus_id' => $unique_syllabus_id))->result_array();
        $max = 0;
        foreach ($rows as $row) {
            foreach ($this->_parse_require_lecture_numbers($row['require_lectures']) as $n) {
                if ($n > $max) $max = $n;
            }
        }
        return $max;
    }

    private function _matches_weekday($date, $day)
    {
        $map = array(
            'monday' => '1', 'tuesday' => '2', 'wednesday' => '3', 'thursday' => '4',
            'friday' => '5', 'saturday' => '6', 'sunday' => '0',
        );
        $d = strtolower(trim($day));
        $want = isset($map[$d]) ? $map[$d] : $day;
        return date('w', strtotime($date)) === $want;
    }

    private function _build_lecture_dates($lecture, $start_date, $max_lectures)
    {
        $days = array_filter(array_map('trim', explode(',', $lecture['days'])));
        $slots = array();
        $i = 0;
        foreach (range(0, 366) as $day) {
            if ($i >= $max_lectures) break;
            $internal_date = date('Y-m-d', strtotime($start_date.' + '.$day.' days'));
            foreach ($days as $tday) {
                $holiday = $this->ci->db->get_where('holidays', array('date' => $internal_date))->row_array();
                if ($this->_matches_weekday($internal_date, $tday) && !$holiday) {
                    $slots[] = array(
                        'date' => $internal_date,
                        'day' => strtoupper($tday),
                    );
                    $i++;
                    if ($i >= $max_lectures) break 2;
                }
            }
        }
        return $slots;
    }

    /** Quiz/test days are extra calendar slots — they do not replace content lectures. */
    private function _count_quiz_slots_for_lectures($max_lectures, $test_after)
    {
        $max_lectures = (int) $max_lectures;
        $test_after = (int) $test_after;
        if ($max_lectures <= 0 || $test_after < 1) return 0;

        $quiz_count = 0;
        $t = 1;
        for ($n = 0; $n < $max_lectures; $n++) {
            $t++;
            if ($t > $test_after) {
                $quiz_count++;
                $t = 1;
            }
        }
        return $quiz_count;
    }

    private function _map_template_for_lecture($template_rows, $lecture_no)
    {
        $topic_ids = array();
        $practical_ids = array();
        foreach ($template_rows as $row) {
            $nums = $this->_parse_require_lecture_numbers($row['require_lectures']);
            if (!in_array($lecture_no, $nums, true)) continue;
            if (!empty($row['topic_id'])) $topic_ids[] = (int) $row['topic_id'];
            if (!empty($row['practical_id'])) $practical_ids[] = (int) $row['practical_id'];
        }

        $topic_names = array();
        if (count($topic_ids)) {
            $topics = $this->ci->db->where_in('topic_id', $topic_ids)->get('topics')->result_array();
            foreach ($topics as $t) $topic_names[] = $t['topic_name'];
        }
        $practical_names = array();
        if (count($practical_ids)) {
            $practicals = $this->ci->db->where_in('practical_id', $practical_ids)->get('practicals')->result_array();
            foreach ($practicals as $p) $practical_names[] = $p['practical_name'];
        }

        return array(
            'topic_ids' => implode(',', $topic_ids),
            'practical_ids' => implode(',', $practical_ids),
            'topic_names' => $topic_names,
            'practical_names' => $practical_names,
        );
    }

    private function _accumulate_quiz_slot(&$ids, &$names, $ids_csv, $names_arr)
    {
        $id_list = array_filter(array_map('intval', explode(',', (string) $ids_csv)));
        foreach ($id_list as $idx => $id) {
            if (!$id || in_array($id, $ids, true)) continue;
            $ids[] = $id;
            $names[] = isset($names_arr[$idx]) ? $names_arr[$idx] : ('#'.$id);
        }
    }

    private function _build_require_lectures($item)
    {
        $mode = isset($item['mode']) ? $item['mode'] : 'single';

        if ($mode === 'range') {
            $from = isset($item['range_from']) ? (int) $item['range_from'] : 0;
            $to = isset($item['range_to']) ? (int) $item['range_to'] : 0;
            if ($from <= 0 || $to <= 0) return array('valid' => false, 'message' => 'Range lectures must be positive');
            if ($to < $from) return array('valid' => false, 'message' => 'Range end must be >= start');
            $numbers = range($from, $to);
            return array(
                'valid' => true,
                'require_lectures' => implode('-', $numbers),
                'numbers' => $numbers,
            );
        }

        if ($mode === 'count') {
            $start = isset($item['count_start']) ? (int) $item['count_start'] : 0;
            $count = isset($item['count_num']) ? (int) $item['count_num'] : 0;
            if ($start <= 0 || $count <= 0) return array('valid' => false, 'message' => 'Count and start lecture must be positive');
            $numbers = range($start, $start + $count - 1);
            return array(
                'valid' => true,
                'require_lectures' => implode('-', $numbers),
                'numbers' => $numbers,
            );
        }

        $single = isset($item['single_lecture']) ? (int) $item['single_lecture'] : 0;
        if ($single <= 0) return array('valid' => false, 'message' => 'Lecture number must be positive');
        return array(
            'valid' => true,
            'require_lectures' => (string) $single,
            'numbers' => array($single),
        );
    }

    // ── Timetable: Study Type ────────────────────────────────────────────────

    public function study_type_list()
    {
        return $this->ci->db
            ->select('study_type.*, courses.course_name, users.first_name, users.last_name')
            ->from('study_type')
            ->join('users', 'users.user_id = study_type.created_by', 'left')
            ->join('courses', 'courses.course_id = study_type.course_id', 'left')
            ->order_by('study_type.id', 'DESC')
            ->get()->result_array();
    }

    public function save_study_type($user, $payload)
    {
        $course_id = isset($payload['course_id']) ? (int) $payload['course_id'] : 0;
        $name = isset($payload['name']) ? trim($payload['name']) : '';
        $days = isset($payload['days']) ? $payload['days'] : array();
        if ($course_id <= 0) return array('success' => false, 'message' => 'Course is required');
        if ($name === '') return array('success' => false, 'message' => 'Name is required');
        if (!is_array($days) || !count($days)) return array('success' => false, 'message' => 'Select at least one day');
        $this->ci->db->insert('study_type', array(
            'course_id' => $course_id,
            'name' => $name,
            'days' => implode(',', $days),
            'created_by' => $user['user_id'],
        ));
        return array('success' => true, 'message' => 'Study type saved', 'id' => (int) $this->ci->db->insert_id());
    }

    public function update_study_type($user, $payload)
    {
        $id = isset($payload['id']) ? (int) $payload['id'] : 0;
        $course_id = isset($payload['course_id']) ? (int) $payload['course_id'] : 0;
        $name = isset($payload['name']) ? trim($payload['name']) : '';
        $days = isset($payload['days']) ? $payload['days'] : array();
        if ($id <= 0) return array('success' => false, 'message' => 'Invalid study type');
        if ($course_id <= 0) return array('success' => false, 'message' => 'Course is required');
        if ($name === '') return array('success' => false, 'message' => 'Name is required');
        if (!is_array($days) || !count($days)) return array('success' => false, 'message' => 'Select at least one day');
        $this->ci->db->where('id', $id)->update('study_type', array(
            'course_id' => $course_id,
            'name' => $name,
            'days' => implode(',', $days),
        ));
        return array('success' => true, 'message' => 'Study type updated');
    }

    public function delete_study_type($id, $user)
    {
        if (!$user || $user['role'] !== 'Admin') {
            return array('success' => false, 'message' => 'Admin permission required to delete study type');
        }
        $id = (int) $id;
        if ($id <= 0) return array('success' => false, 'message' => 'Invalid study type');
        $this->ci->db->where('id', $id)->delete('study_type');
        return array('success' => true, 'message' => 'Study type deleted');
    }

    // ── Timetable: Shifts ────────────────────────────────────────────────────

    public function shifts_list()
    {
        $rows = $this->ci->db
            ->select('shifts.*, users.first_name, users.last_name, shifts.campus_id as shift_campus, study_type.name as study_type_name, courses.course_id as shift_course, courses.course_name')
            ->from('shifts')
            ->join('users', 'users.user_id = shifts.created_by', 'left')
            ->join('study_type', 'study_type.id = shifts.study_type_id', 'left')
            ->join('courses', 'study_type.course_id = courses.course_id', 'left')
            ->order_by('shifts.id', 'DESC')
            ->get()->result_array();

        $campus_map = array();
        foreach ($this->ci->db->get('campuses')->result_array() as $c) {
            $campus_map[(int) $c['campus_id']] = $c['campus_name'];
        }

        $out = array();
        foreach ($rows as $row) {
            $campus_names = array();
            foreach (array_filter(explode(',', $row['shift_campus'])) as $cid) {
                $cid = (int) $cid;
                if (isset($campus_map[$cid])) $campus_names[] = $campus_map[$cid];
            }
            $out[] = array(
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'campus_ids' => array_values(array_filter(array_map('intval', explode(',', $row['shift_campus'])))),
                'campus_names' => $campus_names,
                'study_type_id' => (int) $row['study_type_id'],
                'study_type_name' => $row['study_type_name'],
                'course_id' => (int) $row['shift_course'],
                'course_name' => $row['course_name'],
                'created_by_name' => trim($row['first_name'].' '.$row['last_name']),
                'created_at' => $row['created_at'],
            );
        }
        return $out;
    }

    public function save_shift($user, $payload)
    {
        $name = isset($payload['name']) ? trim($payload['name']) : '';
        $start_time = isset($payload['start_time']) ? $payload['start_time'] : '';
        $end_time = isset($payload['end_time']) ? $payload['end_time'] : '';
        $campus_ids = isset($payload['campus_ids']) ? $payload['campus_ids'] : array();
        $study_type_id = isset($payload['study_type_id']) ? (int) $payload['study_type_id'] : 0;
        if ($name === '') return array('success' => false, 'message' => 'Shift name is required');
        if ($start_time === '' || $end_time === '') return array('success' => false, 'message' => 'Start and end time are required');
        if (!is_array($campus_ids) || !count($campus_ids)) return array('success' => false, 'message' => 'Select at least one campus');
        if ($study_type_id <= 0) return array('success' => false, 'message' => 'Study type is required');
        $this->ci->db->insert('shifts', array(
            'name' => $name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'campus_id' => implode(',', array_map('intval', $campus_ids)),
            'study_type_id' => $study_type_id,
            'created_by' => $user['user_id'],
        ));
        return array('success' => true, 'message' => 'Shift saved', 'id' => (int) $this->ci->db->insert_id());
    }

    public function update_shift($user, $payload)
    {
        $id = isset($payload['id']) ? (int) $payload['id'] : 0;
        $name = isset($payload['name']) ? trim($payload['name']) : '';
        $start_time = isset($payload['start_time']) ? $payload['start_time'] : '';
        $end_time = isset($payload['end_time']) ? $payload['end_time'] : '';
        $campus_ids = isset($payload['campus_ids']) ? $payload['campus_ids'] : array();
        $study_type_id = isset($payload['study_type_id']) ? (int) $payload['study_type_id'] : 0;
        if ($id <= 0) return array('success' => false, 'message' => 'Invalid shift');
        if ($name === '') return array('success' => false, 'message' => 'Shift name is required');
        if ($start_time === '' || $end_time === '') return array('success' => false, 'message' => 'Start and end time are required');
        if (!is_array($campus_ids) || !count($campus_ids)) return array('success' => false, 'message' => 'Select at least one campus');
        if ($study_type_id <= 0) return array('success' => false, 'message' => 'Study type is required');
        $this->ci->db->where('id', $id)->update('shifts', array(
            'name' => $name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'campus_id' => implode(',', array_map('intval', $campus_ids)),
            'study_type_id' => $study_type_id,
            'created_by' => $user['user_id'],
        ));
        return array('success' => true, 'message' => 'Shift updated');
    }

    public function delete_shift($id)
    {
        $id = (int) $id;
        if ($id <= 0) return array('success' => false, 'message' => 'Invalid shift');
        $this->ci->db->where('id', $id)->delete('shifts');
        return array('success' => true, 'message' => 'Shift deleted');
    }

    // ── Timetable: helpers ───────────────────────────────────────────────────

    public function campus_rooms($campus_id)
    {
        $campus_id = (int) $campus_id;
        if ($campus_id <= 0) return array();
        return $this->ci->db
            ->select('room_id, room_name')
            ->from('rooms')
            ->where('type', '0')
            ->where('campus_id', $campus_id)
            ->order_by('room_name', 'ASC')
            ->get()->result_array();
    }

    public function study_type_days($study_type_id)
    {
        $study_type_id = (int) $study_type_id;
        if ($study_type_id <= 0) return array();
        $row = $this->ci->db->get_where('study_type', array('id' => $study_type_id))->row_array();
        if (!$row) return array();
        return array_values(array_filter(array_map('trim', explode(',', $row['days']))));
    }

    public function course_sessions($course_id)
    {
        $course_id = (int) $course_id;
        if ($course_id <= 0) return array();
        return $this->ci->db
            ->select('session_name')
            ->from('course_sessions')
            ->where('course_id', $course_id)
            ->order_by('session_name', 'ASC')
            ->get()->result_array();
    }

    public function subjects_for_class($course_id, $class)
    {
        $course_id = (int) $course_id;
        $class = (int) $class;
        if ($course_id <= 0 || $class <= 0) return array();
        return $this->ci->db
            ->select('course_subject_id, subject_name')
            ->from('course_subjects')
            ->where(array('course_id' => $course_id, 'status' => 1))
            ->where("(subject_year='$class' OR subject_semester='$class')", null, false)
            ->order_by('subject_name', 'ASC')
            ->get()->result_array();
    }

    public function shifts_for_campus($campus_id, $study_type_id = null)
    {
        $campus_id = (int) $campus_id;
        if ($campus_id <= 0) return array();
        $this->ci->db->where("find_in_set($campus_id, campus_id)", null, false);
        if ($study_type_id !== null && $study_type_id !== '') {
            $this->ci->db->where('study_type_id', (int) $study_type_id);
        }
        return $this->ci->db
            ->select('id, name, start_time, end_time, study_type_id')
            ->from('shifts')
            ->order_by('name', 'ASC')
            ->get()->result_array();
    }

    public function timetable_detail($id)
    {
        $id = (int) $id;
        if ($id <= 0) return null;
        $row = $this->ci->db->get_where('lectures', array('id' => $id))->row_array();
        if (!$row) return null;
        return array(
            'id' => (int) $row['id'],
            'lecture_name' => $row['lecture_name'],
            'campus' => (int) $row['campus'],
            'course' => (int) $row['course'],
            'class' => (string) $row['class'],
            'session' => array_values(array_filter(array_map('trim', explode(',', $row['session'])))),
            'subjects' => array_values(array_filter(array_map('intval', explode(',', $row['subjects'])))),
            'shift' => (int) $row['shift'],
            'studytype' => (int) $row['studytype'],
            'room' => (int) $row['room'],
            'teacher' => (int) $row['teacher'],
            'second_teacher' => (int) $row['second_teacher'],
            'start_time' => $row['start_date'],
            'end_time' => $row['end_date'],
            'days' => array_values(array_filter(array_map('trim', explode(',', $row['days'])))),
        );
    }

    public function save_timetable($user, $payload)
    {
        $parsed = $this->_parse_timetable_payload($payload);
        if (empty($parsed['success'])) return $parsed;
        $data = $parsed['data'];
        $data['created_by'] = $user['user_id'];
        $this->ci->db->insert('lectures', $data);
        return array('success' => true, 'message' => 'Lecture saved', 'id' => (int) $this->ci->db->insert_id());
    }

    public function update_timetable($user, $id, $payload)
    {
        $id = (int) $id;
        if ($id <= 0) return array('success' => false, 'message' => 'Invalid lecture');
        $parsed = $this->_parse_timetable_payload($payload);
        if (empty($parsed['success'])) return $parsed;
        $data = $parsed['data'];
        $data['created_by'] = $user['user_id'];
        $this->ci->db->where('id', $id)->update('lectures', $data);
        return array('success' => true, 'message' => 'Lecture updated', 'id' => $id);
    }

    public function delete_timetable($id)
    {
        $id = (int) $id;
        if ($id <= 0) return array('success' => false, 'message' => 'Invalid lecture');
        $this->ci->db->where('id', $id)->delete('lectures');
        return array('success' => true, 'message' => 'Lecture deleted');
    }

    public function timetable_groups($campus_id = null)
    {
        $this->ci->db->select('lectures.shift, lectures.studytype, lectures.campus, courses.course_name, campuses.campus_name');
        $this->ci->db->from('lectures');
        $this->ci->db->join('courses', 'courses.course_id = lectures.course', 'left');
        $this->ci->db->join('campuses', 'campuses.campus_id = lectures.campus', 'left');
        if ($campus_id !== null && $campus_id !== '' && $campus_id !== 'all') {
            $this->ci->db->where('lectures.campus', (int) $campus_id);
        }
        $this->ci->db->group_by(array('lectures.shift', 'lectures.studytype', 'lectures.campus'));
        $this->ci->db->order_by('campuses.campus_name', 'ASC');
        $rows = $this->ci->db->get()->result_array();

        $out = array();
        foreach ($rows as $row) {
            $shift = $this->ci->db->get_where('shifts', array('id' => $row['shift']))->row_array();
            $study = $this->ci->db->get_where('study_type', array('id' => $row['studytype']))->row_array();
            $out[] = array(
                'shift_id' => (int) $row['shift'],
                'shift_name' => $shift ? $shift['name'] : '',
                'studytype_id' => (int) $row['studytype'],
                'study_type_name' => $study ? $study['name'] : '',
                'campus_id' => (int) $row['campus'],
                'campus_name' => $row['campus_name'],
                'course_name' => $row['course_name'],
                'label' => trim(($shift ? $shift['name'] : '').' - '.($study ? $study['name'] : '').' - '.$row['course_name'].' - '.$row['campus_name']),
            );
        }
        return $out;
    }

    public function assign_zoom($lecture_id, $zoom_id, $zoom_password)
    {
        $lecture_id = (int) $lecture_id;
        if ($lecture_id <= 0) return array('success' => false, 'message' => 'Invalid lecture');
        if (trim($zoom_id) === '') return array('success' => false, 'message' => 'Zoom ID is required');
        $this->ci->db->where('id', $lecture_id)->update('lectures', array(
            'zoom_id' => trim($zoom_id),
            'zoom_password' => trim($zoom_password),
        ));
        return array('success' => true, 'message' => 'Zoom assigned');
    }

    public function lecture_sessions($lecture_id, $mode = 'today')
    {
        $lecture_id = (int) $lecture_id;
        $lecture = $this->_lecture_session_header($lecture_id);
        if (!$lecture) return array('success' => false, 'message' => 'Lecture not found');

        $this->ci->db->select('lectures.id as lecture_id, lectures.session, lectures.studytype, lectures.room, lectures.teacher, lectures.subjects, lectures.shift, lectures.class, lectures.lecture_name, lectures.start_date, lectures.end_date, session_syllabus.date, session_syllabus.topic_ids, session_syllabus.practical_ids, session_syllabus.is_quiz, session_syllabus.is_half, session_syllabus.id as session_syllabus_id, session_syllabus.subject_id, session_syllabus.syllabus_id, courses.course_name, campuses.campus_name, course_subjects.subject_name');
        $this->ci->db->from('session_syllabus');
        $this->ci->db->join('lectures', 'session_syllabus.lecture_id=lectures.id', 'inner');
        $this->ci->db->join('courses', 'courses.course_id=lectures.course', 'inner');
        $this->ci->db->join('campuses', 'campuses.campus_id=lectures.campus', 'inner');
        $this->ci->db->join('course_subjects', 'course_subjects.course_subject_id=session_syllabus.subject_id', 'inner');
        $this->ci->db->where('lectures.id', $lecture_id);
        if ($mode === 'today') {
            $this->ci->db->where('session_syllabus.date <=', date('Y-m-d'));
            $this->ci->db->group_by('session_syllabus.date');
        }
        $this->ci->db->order_by('session_syllabus.date', 'DESC');
        $rows = $this->ci->db->get()->result_array();

        $built = array();
        foreach ($rows as $idx => $row) {
            $is_full = ((int) $row['is_quiz'] === 1 && (int) $row['is_half'] === 0 && $idx === 0);
            $built[] = $this->_build_lecture_session_row($row, $is_full);
        }

        $summary = null;
        $sessions = $built;
        if ($mode === 'today') {
            $total = count($built);
            $pending = 0;
            $done = 0;
            foreach ($built as $b) {
                if ($b['is_pending']) $pending++;
                else $done++;
            }
            usort($built, function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });
            $sessions = $built;

            $visible_count = 0;
            $counter = 0;
            $desc = array_reverse($built);
            foreach ($desc as $b) {
                $counter++;
                if ($counter <= 5 || $b['is_pending']) $visible_count++;
            }

            $summary = array(
                'total_till_today' => $total,
                'pending' => $pending,
                'done' => $done,
                'showing' => $visible_count,
            );
        }

        return array(
            'success' => true,
            'mode' => $mode,
            'lecture' => $lecture,
            'summary' => $summary,
            'sessions' => $sessions,
        );
    }

    public function lecture_attendance_list($lecture_id, $date)
    {
        $lecture_id = (int) $lecture_id;
        if ($lecture_id <= 0 || $date === '') return array();
        return $this->ci->db
            ->select('lecture_wise_attendance.*, students.roll_no, students.first_name, students.last_name, students.cnic, students.mobile, students.emergency_no')
            ->from('lecture_wise_attendance')
            ->join('students', 'students.student_id = lecture_wise_attendance.student_id', 'left')
            ->where('lecture_wise_attendance.lecture_id', $lecture_id)
            ->where('lecture_wise_attendance.date', $date)
            ->order_by('students.roll_no', 'ASC')
            ->get()->result_array();
    }

    public function session_students($lecture_id)
    {
        $lecture_id = (int) $lecture_id;
        $lecture = $this->_lecture_session_header($lecture_id);
        if (!$lecture) return array('success' => false, 'message' => 'Lecture not found');

        $row = $this->ci->db->get_where('lectures', array('id' => $lecture_id))->row_array();
        $study_session = array_filter(explode(',', $row['session']));

        $this->ci->db->select('students.student_id, students.roll_no, students.first_name, students.last_name, students.mobile, students.emergency_no, students.cnic, classes.name as class_name, courses.course_name, campuses.campus_name');
        $this->ci->db->from('students');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'left');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->ci->db->join('courses', 'courses.course_id=students.course_id', 'left');
        $this->ci->db->where('students.status', '1');
        $this->ci->db->where('students.shift', $row['shift']);
        $this->ci->db->where('students.study_type', $row['studytype']);
        $this->ci->db->where('students.study_campus', $row['campus']);
        if (count($study_session)) $this->ci->db->where_in('students.study_session', $study_session);
        $this->ci->db->order_by('students.roll_no', 'ASC');
        $students = $this->ci->db->get()->result_array();

        $today = date('Y-m-d');
        $out = array();
        foreach ($students as $student) {
            $attendance = $this->ci->db->get_where('lecture_wise_attendance', array(
                'student_id' => $student['student_id'],
                'lecture_id' => $lecture_id,
                'date' => $today,
            ))->row_array();
            $absent_logs = $this->ci->db
                ->where('student_id', $student['student_id'])
                ->where('lecture_id', $lecture_id)
                ->order_by('id', 'DESC')
                ->get('lecture_absent_student_logs')
                ->result_array();
            $out[] = array(
                'student_id' => (int) $student['student_id'],
                'roll_no' => $student['roll_no'],
                'name' => trim($student['first_name'].' '.$student['last_name']),
                'mobile' => $student['mobile'],
                'emergency_no' => $student['emergency_no'],
                'cnic' => $student['cnic'],
                'present' => !empty($attendance),
                'attendance_add_by' => $attendance ? $attendance['add_by'] : '',
                'attendance_updated_at' => $attendance ? $attendance['updated_at'] : '',
                'absent_logs' => array_map(function ($log) {
                    return array(
                        'info' => $log['info'],
                        'add_by' => $log['add_by'],
                        'created_at' => $log['created_at'],
                    );
                }, $absent_logs),
            );
        }

        return array(
            'success' => true,
            'lecture' => $lecture,
            'students' => $out,
            'date' => $today,
        );
    }

    public function mark_student_attendance($user, $student_id, $lecture_id)
    {
        $student_id = (int) $student_id;
        $lecture_id = (int) $lecture_id;
        if ($student_id <= 0 || $lecture_id <= 0) return array('success' => false, 'message' => 'Invalid data');
        $this->ci->db->insert('lecture_wise_attendance', array(
            'student_id' => $student_id,
            'lecture_id' => $lecture_id,
            'add_by' => $this->_user_display_name($user),
            'date' => date('Y-m-d'),
        ));
        return array('success' => true);
    }

    public function unmark_student_attendance($student_id, $lecture_id)
    {
        $student_id = (int) $student_id;
        $lecture_id = (int) $lecture_id;
        $this->ci->db->where('student_id', $student_id);
        $this->ci->db->where('lecture_id', $lecture_id);
        $this->ci->db->where('date', date('Y-m-d'));
        $this->ci->db->delete('lecture_wise_attendance');
        return array('success' => true);
    }

    public function save_absent_student_info($user, $student_id, $lecture_id, $info)
    {
        $student_id = (int) $student_id;
        $lecture_id = (int) $lecture_id;
        $info = trim($info);
        if ($student_id <= 0 || $lecture_id <= 0 || $info === '') {
            return array('success' => false, 'message' => 'Student, lecture, and info are required');
        }
        $this->ci->db->insert('lecture_absent_student_logs', array(
            'student_id' => $student_id,
            'lecture_id' => $lecture_id,
            'info' => $info,
            'add_by' => $this->_user_display_name($user),
        ));
        return array('success' => true, 'message' => 'Info saved');
    }

    public function mark_topic_studied($user, $topic_id, $session_syllabus_id, $is_quiz)
    {
        $this->ci->db->insert('study_by_teacher', array(
            'topic_id' => (int) $topic_id,
            'session_syllabus_id' => (int) $session_syllabus_id,
            'is_quiz' => (int) $is_quiz,
            'created_by' => $this->_user_display_name($user),
        ));
        return array('success' => true);
    }

    public function unmark_topic_studied($topic_id, $session_syllabus_id, $is_quiz)
    {
        $this->ci->db->where(array(
            'topic_id' => (int) $topic_id,
            'session_syllabus_id' => (int) $session_syllabus_id,
            'is_quiz' => (int) $is_quiz,
        ))->delete('study_by_teacher');
        return array('success' => true);
    }

    public function mark_practical_studied($user, $practical_id, $session_syllabus_id, $is_quiz)
    {
        $this->ci->db->insert('study_by_teacher', array(
            'practical_id' => (int) $practical_id,
            'session_syllabus_id' => (int) $session_syllabus_id,
            'is_quiz' => (int) $is_quiz,
            'created_by' => $this->_user_display_name($user),
        ));
        return array('success' => true);
    }

    public function unmark_practical_studied($practical_id, $session_syllabus_id, $is_quiz)
    {
        $this->ci->db->where(array(
            'practical_id' => (int) $practical_id,
            'session_syllabus_id' => (int) $session_syllabus_id,
            'is_quiz' => (int) $is_quiz,
        ))->delete('study_by_teacher');
        return array('success' => true);
    }

    private function _user_display_name($user)
    {
        if (!$user) return 'System';
        $name = trim((isset($user['first_name']) ? $user['first_name'] : '').' '.(isset($user['last_name']) ? $user['last_name'] : ''));
        return $name !== '' ? $name : (isset($user['username']) ? $user['username'] : 'User');
    }

    private function _pending_lectures_count($lecture_id)
    {
        $lectures = $this->ci->db
            ->where('lecture_id', $lecture_id)
            ->where('date <=', date('Y-m-d'))
            ->group_by('date')
            ->get('session_syllabus')
            ->result_array();

        $pending = 0;
        foreach ($lectures as $lecture) {
            if ($this->_is_date_pending($lecture_id, $lecture['date'], (int) $lecture['id'], (int) $lecture['is_quiz'])) {
                $pending++;
            }
        }
        return $pending;
    }

    private function _is_date_pending($lecture_id, $date, $session_syllabus_id, $is_quiz)
    {
        $topic_rows = $this->ci->db
            ->where('lecture_id', $lecture_id)
            ->where('date', $date)
            ->where('topic_ids !=', '')
            ->get('session_syllabus')
            ->result_array();
        foreach ($topic_rows as $topic_row) {
            foreach (array_filter(explode(',', $topic_row['topic_ids'])) as $topic_id) {
                if ($topic_id === '') continue;
                $studied = $this->ci->db->get_where('study_by_teacher', array(
                    'topic_id' => $topic_id,
                    'session_syllabus_id' => $session_syllabus_id,
                    'is_quiz' => $is_quiz,
                ))->result_array();
                if (!count($studied)) return true;
            }
        }

        $practical_rows = $this->ci->db
            ->where('lecture_id', $lecture_id)
            ->where('date', $date)
            ->where('practical_ids !=', '')
            ->get('session_syllabus')
            ->result_array();
        foreach ($practical_rows as $practical_row) {
            foreach (array_filter(explode(',', $practical_row['practical_ids'])) as $practical_id) {
                if ($practical_id === '') continue;
                $studied = $this->ci->db->get_where('study_by_teacher', array(
                    'practical_id' => $practical_id,
                    'session_syllabus_id' => $session_syllabus_id,
                    'is_quiz' => $is_quiz,
                ))->result_array();
                if (!count($studied)) return true;
            }
        }
        return false;
    }

    private function _lecture_session_header($lecture_id)
    {
        $row = $this->ci->db
            ->select('lectures.*, courses.course_name, campuses.campus_name, shifts.name as shift_name, study_type.name as study_type_name, rooms.room_name, users.first_name, users.last_name')
            ->from('lectures')
            ->join('courses', 'courses.course_id=lectures.course', 'left')
            ->join('campuses', 'campuses.campus_id=lectures.campus', 'left')
            ->join('shifts', 'shifts.id=lectures.shift', 'left')
            ->join('study_type', 'study_type.id=lectures.studytype', 'left')
            ->join('rooms', 'rooms.room_id=lectures.room', 'left')
            ->join('users', 'users.user_id=lectures.teacher', 'left')
            ->where('lectures.id', (int) $lecture_id)
            ->get()->row_array();
        if (!$row) return null;

        $subject_names = array();
        foreach (array_filter(explode(',', $row['subjects'])) as $sid) {
            $sub = $this->ci->db->get_where('course_subjects', array('course_subject_id' => $sid))->row_array();
            if ($sub) $subject_names[] = $sub['subject_name'];
        }

        return array(
            'id' => (int) $row['id'],
            'lecture_name' => $row['lecture_name'],
            'campus_name' => $row['campus_name'],
            'course_name' => $row['course_name'],
            'class_label' => ((int) $row['class'] === 1) ? '1st Year' : '2nd Year',
            'session' => $row['session'],
            'shift_name' => $row['shift_name'],
            'study_type_name' => $row['study_type_name'],
            'room_name' => $row['room_name'],
            'subjects' => $subject_names,
            'start_time' => $row['start_date'],
            'end_time' => $row['end_date'],
            'teacher_name' => trim($row['first_name'].' '.$row['last_name']),
        );
    }

    private function _build_lecture_session_row($row, $is_full = false)
    {
        $lecture_id = (int) $row['lecture_id'];
        $date = $row['date'];
        $session_syllabus_id = (int) $row['session_syllabus_id'];
        $is_quiz = (int) $row['is_quiz'];
        $is_half = (int) $row['is_half'];

        $topics = array();
        $topic_rows = $this->ci->db
            ->where('lecture_id', $lecture_id)
            ->where('date', $date)
            ->where('topic_ids !=', '')
            ->get('session_syllabus')
            ->result_array();
        foreach ($topic_rows as $topic_row) {
            $topic_ids = array_filter(explode(',', $topic_row['topic_ids']));
            if (!count($topic_ids)) continue;
            $topic_entities = $this->ci->db->where_in('topic_id', $topic_ids)->get('topics')->result_array();
            $subject = $this->ci->db->get_where('course_subjects', array('course_subject_id' => $topic_row['subject_id']))->row_array();
            $syllabus = $this->ci->db->get_where('syllabus', array('unique_syllabus_id' => $topic_row['syllabus_id']))->row_array();
            foreach ($topic_entities as $te) {
                $studied = $this->ci->db->get_where('study_by_teacher', array(
                    'topic_id' => $te['topic_id'],
                    'session_syllabus_id' => $session_syllabus_id,
                    'is_quiz' => $is_quiz,
                ))->row_array();
                $topics[] = array(
                    'topic_id' => (int) $te['topic_id'],
                    'topic_name' => $te['topic_name'],
                    'subject_name' => $subject ? $subject['subject_name'] : '',
                    'syllabus_name' => $syllabus ? $syllabus['syllabus_name'] : '',
                    'studied' => !empty($studied),
                    'studied_by' => $studied ? $studied['created_by'] : '',
                    'studied_at' => $studied ? $studied['created_at'] : '',
                );
            }
        }

        $practicals = array();
        $practical_rows = $this->ci->db
            ->where('lecture_id', $lecture_id)
            ->where('date', $date)
            ->where('practical_ids !=', '')
            ->get('session_syllabus')
            ->result_array();
        foreach ($practical_rows as $practical_row) {
            $practical_ids = array_filter(explode(',', $practical_row['practical_ids']));
            if (!count($practical_ids)) continue;
            $practical_entities = $this->ci->db->where_in('practical_id', $practical_ids)->get('practicals')->result_array();
            $subject = $this->ci->db->get_where('course_subjects', array('course_subject_id' => $practical_row['subject_id']))->row_array();
            $syllabus = $this->ci->db->get_where('syllabus', array('unique_syllabus_id' => $practical_row['syllabus_id']))->row_array();
            foreach ($practical_entities as $pe) {
                $studied = $this->ci->db->get_where('study_by_teacher', array(
                    'practical_id' => $pe['practical_id'],
                    'session_syllabus_id' => $session_syllabus_id,
                    'is_quiz' => $is_quiz,
                ))->row_array();
                $practicals[] = array(
                    'practical_id' => (int) $pe['practical_id'],
                    'practical_name' => $pe['practical_name'],
                    'subject_name' => $subject ? $subject['subject_name'] : '',
                    'syllabus_name' => $syllabus ? $syllabus['syllabus_name'] : '',
                    'studied' => !empty($studied),
                    'studied_by' => $studied ? $studied['created_by'] : '',
                    'studied_at' => $studied ? $studied['created_at'] : '',
                );
            }
        }

        $attendance_date = $date;
        $taken = $this->ci->db->get_where('study_by_teacher', array('session_syllabus_id' => $session_syllabus_id))->row_array();
        if ($taken) $attendance_date = date('Y-m-d', strtotime($taken['created_at']));

        $attendance_count = $this->ci->db
            ->where('lecture_id', $lecture_id)
            ->where('date', $attendance_date)
            ->count_all_results('lecture_wise_attendance');

        return array(
            'session_syllabus_id' => $session_syllabus_id,
            'date' => $date,
            'is_today' => ($date === date('Y-m-d')),
            'is_quiz' => $is_quiz,
            'is_half' => $is_half,
            'is_full' => $is_full ? 1 : 0,
            'quiz_label' => $is_quiz ? ($is_full ? 'Full Book Test' : ($is_half ? 'Half Book Test' : 'Quiz')) : null,
            'topics' => $topics,
            'practicals' => $practicals,
            'attendance_count' => $attendance_count,
            'attendance_date' => $attendance_date,
            'is_pending' => $this->_is_date_pending($lecture_id, $date, $session_syllabus_id, $is_quiz),
        );
    }

    private function _parse_timetable_payload($payload)
    {
        $campus = isset($payload['campus']) ? (int) $payload['campus'] : 0;
        $course = isset($payload['course']) ? (int) $payload['course'] : 0;
        $class = isset($payload['class']) ? (string) $payload['class'] : '';
        $sessions = isset($payload['session']) ? $payload['session'] : array();
        $subjects = isset($payload['subjects']) ? $payload['subjects'] : array();
        $shift = isset($payload['shift']) ? (int) $payload['shift'] : 0;
        $studytype = isset($payload['studytype']) ? (int) $payload['studytype'] : 0;
        $room = isset($payload['room']) ? (int) $payload['room'] : 0;
        $teacher = isset($payload['teacher']) ? (int) $payload['teacher'] : 0;
        $second_teacher = isset($payload['second_teacher']) ? (int) $payload['second_teacher'] : 0;
        $days = isset($payload['days']) ? $payload['days'] : array();
        $lecture_name = isset($payload['lecture_name']) ? trim($payload['lecture_name']) : '';
        $start_time = isset($payload['start_time']) ? $payload['start_time'] : '';
        $end_time = isset($payload['end_time']) ? $payload['end_time'] : '';

        if ($campus <= 0) return array('success' => false, 'message' => 'Campus is required');
        if ($course <= 0) return array('success' => false, 'message' => 'Course is required');
        if ($class === '') return array('success' => false, 'message' => 'Class is required');
        if (!is_array($sessions) || !count($sessions)) return array('success' => false, 'message' => 'Select at least one session');
        if (!is_array($subjects) || !count($subjects)) return array('success' => false, 'message' => 'Select at least one subject');
        if ($shift <= 0) return array('success' => false, 'message' => 'Shift is required');
        if ($studytype <= 0) return array('success' => false, 'message' => 'Study type is required');
        if ($room <= 0) return array('success' => false, 'message' => 'Room is required');
        if ($teacher <= 0) return array('success' => false, 'message' => 'Primary teacher is required');
        if (!is_array($days) || !count($days)) return array('success' => false, 'message' => 'Select at least one day');

        return array(
            'success' => true,
            'data' => array(
                'lecture_name' => $lecture_name,
                'course' => $course,
                'class' => $class,
                'session' => implode(',', array_map('strval', $sessions)),
                'campus' => $campus,
                'subjects' => implode(',', array_map('intval', $subjects)),
                'shift' => $shift,
                'studytype' => $studytype,
                'room' => $room,
                'teacher' => $teacher,
                'second_teacher' => $second_teacher,
                'start_date' => $start_time,
                'end_date' => $end_time,
                'days' => implode(',', array_map('strval', $days)),
            ),
        );
    }
}
