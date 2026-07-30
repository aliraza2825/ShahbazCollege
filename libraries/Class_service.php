<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Class_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    private function access_row($user)
    {
        if (!$user) {
            return null;
        }
        return $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
    }

    public function is_admin($user)
    {
        return $user && $user['role'] === 'Admin';
    }

    public function allowed_class_ids($user)
    {
        if ($this->is_admin($user)) {
            return null;
        }
        $access = $this->access_row($user);
        if (!$access || empty($access['class_ids'])) {
            return array();
        }
        $ids = array();
        foreach (explode(',', $access['class_ids']) as $id) {
            $id = (int) trim($id);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    public function allowed_campus_ids($user)
    {
        if ($this->is_admin($user)) {
            return null;
        }
        $access = $this->access_row($user);
        if (!$access || empty($access['campus_ids'])) {
            return array();
        }
        $ids = array();
        foreach (explode(',', $access['campus_ids']) as $id) {
            $id = (int) trim($id);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    public function can_manage($user)
    {
        if (!$user) {
            return false;
        }
        if ($this->is_admin($user)) {
            return true;
        }
        $access = $this->access_row($user);
        return !empty($access['class_edit']) || !empty($access['class_delete']) || !empty($access['class_ids']);
    }

    private function apply_class_scope($user, $alias = 'classes')
    {
        $ids = $this->allowed_class_ids($user);
        if ($ids === null) {
            return;
        }
        if (!count($ids)) {
            $this->ci->db->where($alias . '.class_id', 0);
            return;
        }
        $this->ci->db->where_in($alias . '.class_id', $ids);
    }

    public function meta($user)
    {
        $campuses = $this->ci->db->where('status', 1)->order_by('campus_name', 'ASC')->get('campuses')->result_array();
        $campus_ids = $this->allowed_campus_ids($user);
        if ($campus_ids !== null) {
            $campuses = array_values(array_filter($campuses, function ($c) use ($campus_ids) {
                return in_array((int) $c['campus_id'], $campus_ids, true);
            }));
        }
        return array('campuses' => $campuses);
    }

    public function courses_for_campus($campus_id)
    {
        $campus_id = (int) $campus_id;
        if ($campus_id <= 0) {
            return array();
        }
        $this->ci->db->from('courses');
        $this->ci->db->like('campus_ids', (string) $campus_id, 'both');
        $this->ci->db->order_by('course_name', 'ASC');
        return $this->ci->db->get()->result_array();
    }

    public function sessions_for_course($course_id)
    {
        $course_id = (int) $course_id;
        if ($course_id <= 0) {
            return array();
        }
        return $this->ci->db
            ->order_by('session_name', 'ASC')
            ->get_where('course_sessions', array('course_id' => $course_id))
            ->result_array();
    }

    public function session_detail($course_id, $session_name)
    {
        $course_id = (int) $course_id;
        $session_name = trim((string) $session_name);
        if ($course_id <= 0 || $session_name === '') {
            return null;
        }
        $row = $this->ci->db->get_where('course_sessions', array(
            'course_id' => $course_id,
            'session_name' => $session_name,
        ))->row_array();
        if (!$row) {
            return null;
        }
        return array(
            'class_fee' => $row['per_student_fee'],
            'minimum_installment_fee' => $row['minimum_installment_fee'],
            'dead_line_entry' => $row['dead_line_add_edit_student'],
            'maximum_fee_last_date' => $row['maximum_fee_last_date'],
            'maximum_difference_installments' => $row['maximum_difference_installments'],
            'exam_no' => $row['first_council_exam_no'],
            'freeze_fee' => $row['freeze_fee'],
            'freeze_last_date' => $row['freeze_last_date'],
            'admission_fee' => $row['re_admission_fee'],
        );
    }

    private function read_body($body)
    {
        return array(
            'campus_id' => (int) (isset($body['campus_id']) ? $body['campus_id'] : 0),
            'course_id' => (int) (isset($body['course_id']) ? $body['course_id'] : 0),
            'session' => isset($body['session']) ? trim($body['session']) : '',
            'name' => isset($body['name']) ? trim($body['name']) : '',
            'badge_no' => isset($body['badge_no']) ? trim($body['badge_no']) : '',
            'seats' => isset($body['seats']) ? trim($body['seats']) : '',
            'online_study' => !empty($body['online_study']) ? 1 : 0,
            'status' => isset($body['status']) ? (int) $body['status'] : 1,
        );
    }

    private function validate_body($fields)
    {
        if ($fields['campus_id'] <= 0) {
            return array('success' => false, 'message' => 'Campus is required');
        }
        if ($fields['course_id'] <= 0) {
            return array('success' => false, 'message' => 'Course is required');
        }
        if ($fields['session'] === '') {
            return array('success' => false, 'message' => 'Session is required');
        }
        if ($fields['name'] === '') {
            return array('success' => false, 'message' => 'Class name is required');
        }
        if ($fields['badge_no'] === '') {
            return array('success' => false, 'message' => 'Badge number is required');
        }
        if ($fields['seats'] === '') {
            return array('success' => false, 'message' => 'Number of seats is required');
        }
        $detail = $this->session_detail($fields['course_id'], $fields['session']);
        if (!$detail) {
            return array('success' => false, 'message' => 'Course session not found');
        }
        return array('success' => true, 'fields' => $fields, 'detail' => $detail);
    }

    private function enrich_row($row)
    {
        if (!$row) {
            return $row;
        }
        $class_id = (int) $row['class_id'];
        $row['active_students'] = (int) $this->ci->db
            ->where(array('class_id' => $class_id, 'status' => 1))
            ->count_all_results('students');
        $row['inactive_students'] = (int) $this->ci->db
            ->where(array('class_id' => $class_id, 'status' => 0))
            ->count_all_results('students');
        $row['online_study'] = !empty($row['online_study']) ? 1 : 0;
        return $row;
    }

    public function list_all($user)
    {
        $this->ci->db->select('classes.*, campuses.campus_name, courses.course_name');
        $this->ci->db->from('classes');
        $this->ci->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'left');
        $this->ci->db->join('courses', 'courses.course_id = classes.course_id', 'left');
        $this->ci->db->where('classes.status', 1);
        $this->apply_class_scope($user);
        $this->ci->db->order_by('classes.class_id', 'ASC');
        $rows = $this->ci->db->get()->result_array();
        return array_map(array($this, 'enrich_row'), $rows);
    }

    public function get($class_id, $user)
    {
        $class_id = (int) $class_id;
        $this->ci->db->select('classes.*, campuses.campus_name, courses.course_name');
        $this->ci->db->from('classes');
        $this->ci->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'left');
        $this->ci->db->join('courses', 'courses.course_id = classes.course_id', 'left');
        $this->ci->db->where('classes.class_id', $class_id);
        $this->apply_class_scope($user);
        $row = $this->ci->db->get()->row_array();
        if (!$row) {
            return null;
        }
        return $this->enrich_row($row);
    }

    public function create($user, $body)
    {
        if (!$this->is_admin($user)) {
            $access = $this->access_row($user);
            if (empty($access['class_edit'])) {
                return array('success' => false, 'message' => 'Class add permission required');
            }
        }
        $fields = $this->read_body($body);
        $parsed = $this->validate_body($fields);
        if (empty($parsed['success'])) {
            return $parsed;
        }
        $data = array_merge($parsed['fields'], $parsed['detail']);
        $this->ci->db->insert('classes', $data);
        $id = (int) $this->ci->db->insert_id();
        return array('success' => true, 'message' => 'Class added successfully.', 'class_id' => $id, 'data' => $this->get($id, $user));
    }

    public function update($class_id, $user, $body)
    {
        $class_id = (int) $class_id;
        if (!$this->get($class_id, $user)) {
            return array('success' => false, 'message' => 'Class not found');
        }
        if (!$this->is_admin($user)) {
            $access = $this->access_row($user);
            if (empty($access['class_edit'])) {
                return array('success' => false, 'message' => 'Class edit permission required');
            }
        }
        $fields = $this->read_body($body);
        $parsed = $this->validate_body($fields);
        if (empty($parsed['success'])) {
            return $parsed;
        }
        $data = array_merge($parsed['fields'], $parsed['detail']);
        $this->ci->db->where('class_id', $class_id);
        $this->ci->db->update('classes', $data);
        return array('success' => true, 'message' => 'Class updated successfully.', 'data' => $this->get($class_id, $user));
    }

    public function delete($class_id, $user, $actor_name = 'POS')
    {
        $class_id = (int) $class_id;
        if (!$this->get($class_id, $user)) {
            return array('success' => false, 'message' => 'Class not found');
        }
        if (!$this->is_admin($user)) {
            $access = $this->access_row($user);
            if (empty($access['class_delete'])) {
                return array('success' => false, 'message' => 'Class delete permission required');
            }
        }

        $this->ci->db->set('status', 0);
        $this->ci->db->where('class_id', $class_id);
        $this->ci->db->update('classes');

        $this->ci->db->set('status', 0);
        $this->ci->db->where('class_id', $class_id);
        $this->ci->db->update('students');

        $students = $this->ci->db->get_where('students', array('class_id' => $class_id))->result_array();
        foreach ($students as $student) {
            $this->ci->db->insert('deleted_students', array(
                'delete_type' => 'Delete',
                'student_id' => $student['student_id'],
                'deleted_by' => $actor_name,
                'reason' => 'other',
                'refund_amount' => 0,
                'reason_detail' => 'Class Delete',
                'image' => '',
                'approve_by' => $actor_name,
                'status' => 1,
            ));
        }

        return array('success' => true, 'message' => 'Class deleted successfully.');
    }

    /** Legacy classes/students — active students in class (requires machine_data row). */
    public function students_for_class($class_id, $user)
    {
        $class = $this->get($class_id, $user);
        if (!$class) {
            return null;
        }

        $this->ci->db->select(
            'students.student_id, students.roll_no, students.first_name, students.last_name, students.cnic, students.mobile, students.emergency_no, students.registration_date, classes.name as class_name, campuses.campus_name, courses.course_name, machine_data.machine_id',
            false
        );
        $this->ci->db->from('students');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->ci->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'inner');
        $this->ci->db->join('courses', 'courses.course_id=students.course_id', 'inner');
        $this->ci->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id AND machine_data.type="student"', 'inner');
        $this->ci->db->where(array('students.status' => '1', 'students.class_id' => (int) $class_id));
        $this->ci->db->order_by('CAST(students.roll_no AS UNSIGNED)', 'ASC', false);
        $this->ci->db->order_by('students.roll_no', 'ASC');
        $students = $this->ci->db->get()->result_array();

        return array('class' => $class, 'students' => $students);
    }

    /**
     * Legacy classes/attendence — date grid with Present/Absent per student per day.
     * @return array|null
     */
    public function attendance_for_class($class_id, $user, $start_date, $end_date)
    {
        $pack = $this->students_for_class($class_id, $user);
        if (!$pack) {
            return null;
        }

        $start_date = trim((string) $start_date);
        $end_date = trim((string) $end_date);
        if ($start_date === '') {
            $start_date = date('Y-m-d', strtotime('-1 week'));
        }
        if ($end_date === '') {
            $end_date = date('Y-m-d');
        }
        if ($start_date > $end_date) {
            $tmp = $start_date;
            $start_date = $end_date;
            $end_date = $tmp;
        }

        $dates = array();
        try {
            $period = new DatePeriod(
                new DateTime($start_date),
                new DateInterval('P1D'),
                (new DateTime($end_date))->modify('+1 day')
            );
            foreach ($period as $dt) {
                $dates[] = $dt->format('Y-m-d');
            }
        } catch (Exception $e) {
            $dates = array();
        }
        if (count($dates) > 62) {
            $dates = array_slice($dates, 0, 62);
            $end_date = $dates[count($dates) - 1];
        }

        $students = $pack['students'];
        $student_ids = array_map(function ($s) { return (int) $s['student_id']; }, $students);
        $machine_ids = array_values(array_unique(array_filter(array_map(function ($s) {
            return isset($s['machine_id']) ? (int) $s['machine_id'] : 0;
        }, $students))));

        $photos = $this->_photo_urls_by_student($student_ids);
        $attendance_by_machine = $this->_attendance_first_by_machine_day($machine_ids, $start_date, $end_date);

        $rows = array();
        foreach ($students as $s) {
            $sid = (int) $s['student_id'];
            $mid = isset($s['machine_id']) ? (int) $s['machine_id'] : 0;
            $reg = isset($s['registration_date']) ? substr($s['registration_date'], 0, 10) : '';
            $days = array();
            foreach ($dates as $d) {
                if ($reg === '' || $reg >= $d) {
                    $days[$d] = array('status' => 'na');
                    continue;
                }
                $hit = ($mid > 0 && isset($attendance_by_machine[$mid][$d])) ? $attendance_by_machine[$mid][$d] : null;
                if ($hit) {
                    $days[$d] = array(
                        'status' => 'present',
                        'time' => $hit['time'],
                        'campus_name' => isset($hit['campus_name']) ? $hit['campus_name'] : '',
                    );
                } else {
                    $days[$d] = array('status' => 'absent');
                }
            }
            $rows[] = array(
                'student_id' => $sid,
                'roll_no' => $s['roll_no'],
                'first_name' => $s['first_name'],
                'last_name' => $s['last_name'],
                'mobile' => $s['mobile'],
                'emergency_no' => $s['emergency_no'],
                'registration_date' => $reg,
                'campus_name' => $s['campus_name'],
                'course_name' => $s['course_name'],
                'class_name' => $s['class_name'],
                'machine_id' => $mid,
                'photo_url' => isset($photos[$sid]) ? $photos[$sid] : null,
                'days' => $days,
            );
        }

        return array(
            'class' => $pack['class'],
            'start_date' => $start_date,
            'end_date' => $end_date,
            'dates' => $dates,
            'students' => $rows,
        );
    }

    private function _photo_urls_by_student($student_ids)
    {
        $map = array();
        if (!count($student_ids)) {
            return $map;
        }
        $base = rtrim(base_url(), '/');
        $rows = $this->ci->db
            ->select('student_id, image, online_image')
            ->where_in('student_id', $student_ids)
            ->where('type', 'Photo')
            ->get('student_documents')
            ->result_array();
        foreach ($rows as $r) {
            $sid = (int) $r['student_id'];
            if (!empty($r['online_image'])) {
                $map[$sid] = $r['online_image'];
            } elseif (!empty($r['image'])) {
                $map[$sid] = $base . '/uploads/' . rawurlencode($r['image']);
            }
        }
        return $map;
    }

    private function _attendance_first_by_machine_day($machine_ids, $start_date, $end_date)
    {
        $map = array();
        if (!count($machine_ids) || !$this->ci->db->table_exists('attendence')) {
            return $map;
        }

        $this->ci->db->select('attendence.machine_user_id, attendence.time, attendence.campus_code, campuses.campus_name', false);
        $this->ci->db->from('attendence');
        $this->ci->db->join('campuses', 'campuses.campus_code=attendence.campus_code', 'left');
        $this->ci->db->where_in('attendence.machine_user_id', $machine_ids);
        $this->ci->db->where('attendence.time >=', $start_date . ' 00:00:00');
        $this->ci->db->where('attendence.time <=', $end_date . ' 23:59:59');
        $this->ci->db->order_by('attendence.time', 'ASC');
        $rows = $this->ci->db->get()->result_array();

        foreach ($rows as $r) {
            $mid = (int) $r['machine_user_id'];
            $d = substr($r['time'], 0, 10);
            if (!isset($map[$mid][$d])) {
                $map[$mid][$d] = $r;
            }
        }
        return $map;
    }
}
