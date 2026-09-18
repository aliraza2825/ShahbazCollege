<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Punjab_council_service {

    const HOW_TO_USE_MODULE = 'punjab_council_roll_number';

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->helper('custom');
        $this->ci->load->model('student');
        $this->ci->load->model('clas');
    }

    public function can_manage($user)
    {
        if (!$user) return false;
        if ($user['role'] === 'Admin') return true;
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        return $acc && !empty($acc['punjab_pharmacy_council_access']);
    }

    public function permissions($user)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : ($this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array() ?: array());
        return array(
            'is_admin' => $is_admin,
            'module_access' => $is_admin || !empty($acc['punjab_pharmacy_council_access']),
            'enter_roll_no' => $is_admin || !empty($acc['enter_punjab_council_roll_no']),
            'enter_result' => $is_admin || !empty($acc['enter_punjab_council_result']),
            'final_result' => $is_admin || !empty($acc['final_result_pharmacy_technician']),
            'add_council_fee' => $is_admin || !empty($acc['add_council_fee']),
            'next_exam_status' => $is_admin || !empty($acc['next_exam_status']),
            'exam_sequence' => $is_admin,
            'status_report' => $is_admin,
            'conciliation' => $is_admin,
        );
    }

    public function meta($user)
    {
        $perms = $this->permissions($user);
        $campuses = $this->_campuses_for_user($user);
        return array(
            'permissions' => $perms,
            'campuses' => $campuses,
            'council_exam_numbers' => $this->council_exam_numbers(),
            'exam_sequences' => $this->ci->db->order_by('id', 'DESC')->get('exam_sequence')->result_array(),
            'courses' => $this->ci->db->get_where('courses', array('status' => 'Active'))->result_array(),
        );
    }

    // --- How To Use ---

    public function how_to_use_list()
    {
        if (!$this->ci->db->table_exists('how_to_use')) return array();
        return $this->ci->db->order_by('id', 'DESC')->get_where('how_to_use', array('module' => self::HOW_TO_USE_MODULE))->result_array();
    }

    public function how_to_use_add($user)
    {
        if (!$this->ci->db->table_exists('how_to_use')) {
            return array('success' => false, 'message' => 'how_to_use table missing');
        }
        $title = trim($this->ci->input->post('title'));
        if ($title === '') return array('success' => false, 'message' => 'Title required');
        $upload = $this->_upload_file('picture', 'how_to_use');
        if ($upload === false) return array('success' => false, 'message' => 'File upload failed');
        $name = trim((isset($user['first_name']) ? $user['first_name'] : '') . ' ' . (isset($user['last_name']) ? $user['last_name'] : ''));
        $this->ci->db->insert('how_to_use', array(
            'title' => $title,
            'file_type' => (isset($_FILES['picture']['type']) ? $_FILES['picture']['type'] : ''),
            'file' => $upload,
            'module' => self::HOW_TO_USE_MODULE,
            'created_by' => $name !== '' ? $name : 'Admin',
        ));
        return array('success' => true, 'id' => $this->ci->db->insert_id());
    }

    public function how_to_use_delete($id)
    {
        $row = $this->ci->db->get_where('how_to_use', array('id' => (int) $id, 'module' => self::HOW_TO_USE_MODULE))->row_array();
        if (!$row) return array('success' => false, 'message' => 'Not found');
        if (!empty($row['file']) && file_exists(FCPATH . 'how_to_use/' . $row['file'])) {
            @unlink(FCPATH . 'how_to_use/' . $row['file']);
        }
        $this->ci->db->delete('how_to_use', array('id' => (int) $id));
        return array('success' => true);
    }

    // --- Exam sequence ---

    public function exam_sequences_list()
    {
        return $this->ci->db->order_by('id', 'DESC')->get('exam_sequence')->result_array();
    }

    public function exam_sequence_get($id)
    {
        return $this->ci->db->get_where('exam_sequence', array('id' => (int) $id))->row_array();
    }

    public function exam_sequence_save($body, $id = null)
    {
        $post = array_merge(is_array($body) ? $body : array(), $this->ci->input->post() ? $this->ci->input->post() : array());
        $type = isset($post['type']) ? trim($post['type']) : '';
        if ($type === '') return array('success' => false, 'message' => 'Type required');
        $row = array(
            'type' => $type,
            'first_year' => (int) (isset($post['first_year']) ? $post['first_year'] : 0),
            'second_year' => (int) (isset($post['second_year']) ? $post['second_year'] : 0),
        );
        if ($id) {
            $doc_fields = array(
                'first_year_roll_no', 'first_year_date_sheet', 'first_year_date_sheet_nts', 'first_year_result',
                'second_year_roll_no', 'second_year_date_sheet', 'second_year_date_sheet_nts', 'second_year_result',
            );
            foreach ($doc_fields as $field) {
                $upload = $this->_upload_file($field, 'exam_sequence_documents', false);
                if ($upload) {
                    $row[$field] = $upload;
                } elseif (!empty($post['old_' . $field])) {
                    $row[$field] = $post['old_' . $field];
                }
            }
            $this->ci->db->where('id', (int) $id)->update('exam_sequence', $row);
            return array('success' => true, 'id' => (int) $id);
        }
        $this->ci->db->insert('exam_sequence', $row);
        return array('success' => true, 'id' => $this->ci->db->insert_id());
    }

    // --- Roll numbers (pending results) ---

    public function roll_numbers_pending($user)
    {
        $this->ci->db->select('campuses.campus_id, campuses.campus_name, punjab_council_roll_number.*, students.class_id, classes.name as class_name, students.mobile, students.emergency_no, contractors.name as contractor_name, students.roll_no as college_roll_no');
        $this->ci->db->from('punjab_council_roll_number');
        $this->ci->db->join('students', 'students.cnic=punjab_council_roll_number.cnic', 'left');
        $this->ci->db->join('classes', 'students.class_id=classes.class_id', 'left');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
        $this->ci->db->join('contracts', 'students.contract_id=contracts.contract_id', 'left');
        $this->ci->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'left');
        $this->ci->db->where('punjab_council_roll_number.result_remarks', '');
        $this->_apply_campus_scope($user);
        return $this->ci->db->get()->result_array();
    }

    public function upload_roll_csv($post)
    {
        $upload = $this->_upload_file('roll_no', 'results');
        if (!$upload) return array('success' => false, 'message' => 'CSV upload failed');
        $path = FCPATH . 'results/' . $upload;
        if (!file_exists($path)) return array('success' => false, 'message' => 'Uploaded file missing');
        $file = fopen($path, 'r');
        $row = 1;
        while (!feof($file)) {
            $index = fgetcsv($file);
            if (!$index || !isset($index[0])) { $row++; continue; }
            $cnic = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', (isset($index[5]) ? $index[5] : ''));
            $check = $this->ci->db->get_where('punjab_council_roll_number', array(
                'council_exam_no' => $post['council_exam_no'],
                'class' => $post['class'],
                'course_id' => (isset($post['course_id']) ? $post['course_id'] : 1),
                'cnic' => $cnic,
            ))->result_array();
            if ($row !== 1 && $index[0] !== null && $index[0] !== '') {
                $payload = array(
                    'class' => preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $post['class']),
                    'course_id' => preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', (isset($post['course_id']) ? $post['course_id'] : 1)),
                    'roll_no' => preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $index[0]),
                    'computer_no' => preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', (isset($index[4]) ? $index[4] : '')),
                    'cnic' => $cnic,
                    'name' => preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', (isset($index[6]) ? $index[6] : '')),
                    'address' => (isset($index[7]) ? $index[7] : ''),
                    'remarks' => preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', (isset($index[9]) ? $index[9] : '')),
                );
                if (count($check) > 0) {
                    $this->ci->db->where('id', $check[0]['id'])->update('punjab_council_roll_number', $payload);
                } else {
                    $payload['council_exam_no'] = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $post['council_exam_no']);
                    $this->ci->db->insert('punjab_council_roll_number', $payload);
                }
            }
            $row++;
        }
        fclose($file);
        return array('success' => true, 'message' => 'Roll numbers imported');
    }

    public function upload_roll_slip_images($post)
    {
        $exam_no = $post['council_exam_no'];
        $class = $post['class'];
        $count = 0;
        if (empty($_FILES['files']['name']) || !is_array($_FILES['files']['name'])) {
            return array('success' => false, 'message' => 'No files uploaded');
        }
        foreach ($_FILES['files']['name'] as $key => $name) {
            if (empty($name)) continue;
            $tmp = $_FILES['files']['tmp_name'][$key];
            $mime = !empty($_FILES['files']['type'][$key]) ? $_FILES['files']['type'][$key] : 'application/octet-stream';
            $this->ci->load->library('s3_direct_storage');
            $stored = $this->ci->s3_direct_storage->put_file($tmp, 'rollno_slips', basename($name), $mime);
            if ($stored === false) continue;
            $image_name = strtok(basename($name), '.');
            $this->ci->db->set('slip_image', $stored);
            $this->ci->db->where("council_exam_no = '$exam_no' and class = '$class' and roll_no = '$image_name'");
            $this->ci->db->update('punjab_council_roll_number');
            $count++;
        }
        return array('success' => true, 'message' => "$count slip images linked");
    }

    // --- Results ---

    public function results_list($council_exam_no, $class, $user)
    {
        if (!$council_exam_no || !$class) return array();
        $this->ci->db->select('campuses.campus_name, punjab_council_roll_number.*, students.class_id, classes.name as class_name, students.mobile, students.emergency_no, contractors.name as contractor_name, students.roll_no as college_roll_no');
        $this->ci->db->from('punjab_council_roll_number');
        $this->ci->db->join('students', 'students.cnic=punjab_council_roll_number.cnic', 'left');
        $this->ci->db->join('classes', 'students.class_id=classes.class_id', 'left');
        $this->ci->db->join('contractors', 'students.contractor_id=contractors.contractor_id', 'left');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
        $this->ci->db->where(array(
            'punjab_council_roll_number.council_exam_no' => $council_exam_no,
            'punjab_council_roll_number.class' => $class,
        ));
        $this->_apply_campus_scope($user);
        return $this->ci->db->get()->result_array();
    }

    public function upload_result_csv($post)
    {
        // Result CSV is only import input; read PHP's upload stream directly.
        // Moving it into FCPATH/results first was unreliable on production and
        // could make fopen() receive a missing path after a successful upload.
        $path = isset($_FILES['roll_no']['tmp_name']) ? $_FILES['roll_no']['tmp_name'] : '';
        if (!$path || !is_readable($path)) {
            $upload_error = isset($_FILES['roll_no']['error']) ? (int) $_FILES['roll_no']['error'] : null;
            return array('success' => false, 'message' => $upload_error ? 'CSV upload failed (error ' . $upload_error . ')' : 'CSV upload failed');
        }
        $file = @fopen($path, 'r');
        if (!$file) return array('success' => false, 'message' => 'Unable to read uploaded CSV');

        $csv_rows = array();
        while (($row = fgetcsv($file)) !== false) {
            if (!$row || !isset($row[0])) continue;
            // Excel sometimes prefixes the first CSV cell with a UTF-8 BOM.
            $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $row[0]);
            if (trim((string) $row[0]) !== '') $csv_rows[] = $row;
        }
        fclose($file);
        if (!count($csv_rows)) return array('success' => false, 'message' => 'CSV contains no result rows');

        // Standard sheet: A = Roll No, B = Result, C = Remarks. Keep support for
        // the older sheet where the result was supplied in the third column.
        $first = $csv_rows[0];
        $headers = array_map(function ($value) {
            return strtolower(trim((string) $value));
        }, $first);
        $has_header = in_array('roll no', $headers) || in_array('roll_no', $headers) || in_array('roll number', $headers);
        $result_column = 1;
        if ($has_header) {
            foreach ($headers as $key => $header) {
                if (in_array($header, array('result', 'status', 'result remarks', 'remarks'))) {
                    $result_column = $key;
                    break;
                }
            }
            array_shift($csv_rows);
        }

        $class = isset($post['class']) ? (int) $post['class'] : 0;
        $exam = isset($post['council_exam_no']) ? (int) $post['council_exam_no'] : 0;
        $course_id = isset($post['course_id']) ? (int) $post['course_id'] : 1;
        if (!$class || !$exam) return array('success' => false, 'message' => 'Class and council exam are required');

        // Fetch candidates once. The previous code made 2-3 SQL queries per CSV
        // row, which caused large result sheets to hit PHP's 120 second timeout.
        $records = $this->ci->db
            ->select('id, roll_no, cnic')
            // Legacy rows use both NULL and an empty string for pending results.
            // Do not filter result_remarks here: the previous importer updated a
            // matching roll even when it was re-uploaded for correction.
            ->where(array('class' => $class, 'council_exam_no' => $exam, 'course_id' => $course_id))
            ->get('punjab_council_roll_number')
            ->result_array();
        $by_roll = array();
        foreach ($records as $record) $by_roll[trim((string) $record['roll_no'])] = $record;

        $updates = array();
        $passed_cnics = array();
        $unmatched = 0;
        $date = date('Y-m-d');
        foreach ($csv_rows as $index) {
            $roll = trim((string) (isset($index[0]) ? $index[0] : ''));
            if ($roll === '') continue;
            $result = trim((string) (isset($index[$result_column]) ? $index[$result_column] : ''));
            if (!$has_header && count($index) > 2 && !preg_match('/^(pass\*?|fail|absent)$/i', $result)) {
                $result = trim((string) $index[2]);
            }
            if ($result === '') continue;
            if (!isset($by_roll[$roll])) {
                $unmatched++;
                continue;
            }
            $record = $by_roll[$roll];
            $updates[] = array('id' => $record['id'], 'result_remarks' => $result, 'result_update_date' => $date);
            if (preg_match('/^pass\*?$/i', $result) && !empty($record['cnic'])) $passed_cnics[] = $record['cnic'];
        }

        if (!count($updates)) {
            return array('success' => false, 'message' => 'No matching pending roll numbers found in this CSV');
        }

        $this->ci->db->trans_start();
        $this->ci->db->update_batch('punjab_council_roll_number', $updates, 'id');
        if ($class === 1 && count($passed_cnics)) {
            $this->ci->db->where('course_id', $course_id);
            $this->ci->db->where_in('cnic', array_values(array_unique($passed_cnics)));
            $this->ci->db->update('students', array('section' => 'Second Year'));
        }
        $this->ci->db->trans_complete();
        if ($this->ci->db->trans_status() === false) return array('success' => false, 'message' => 'Unable to save CSV results');

        $message = count($updates) . ' results updated';
        if ($unmatched) $message .= '; ' . $unmatched . ' roll number(s) not matched';
        return array('success' => true, 'message' => $message, 'updated' => count($updates), 'unmatched' => $unmatched);
    }

    public function upload_result_card_images($post)
    {
        $class = $post['class'];
        $exam = $post['council_exam_no'];
        $s3dir = 'results/result_' . $class . '_' . $exam;
        $this->ci->load->library('s3_direct_storage');
        $linked = 0;
        if (empty($_FILES['filefield']['name']) || !is_array($_FILES['filefield']['name'])) {
            return array('success' => false, 'message' => 'No images uploaded');
        }
        foreach ($_FILES['filefield']['name'] as $key => $name) {
            if (empty($name)) continue;
            $roll = strtok($name, '.');
            $exists = $this->ci->db->get_where('punjab_council_roll_number', array(
                'roll_no' => $roll, 'class' => $class, 'course_id' => (isset($post['course_id']) ? $post['course_id'] : 1), 'council_exam_no' => $exam,
            ))->result_array();
            if (!count($exists)) continue;
            $stored = $this->ci->s3_direct_storage->put_file($_FILES['filefield']['tmp_name'][$key], $s3dir, basename($name), 'image/jpeg');
            if ($stored !== false) {
                $this->ci->db->set('result_image', $s3dir . '/' . $stored);
                $this->ci->db->where('roll_no', $roll);
                $this->ci->db->where('class', $class);
                $this->ci->db->where('council_exam_no', $exam);
                $this->ci->db->update('punjab_council_roll_number');
                $linked++;
            }
        }
        return array('success' => true, 'message' => "$linked result cards linked");
    }

    public function update_cnic($id, $cnic, $council_mistake)
    {
        $this->ci->db->set('cnic', $cnic);
        $this->ci->db->set('council_mistake', $council_mistake ? 1 : 0);
        $this->ci->db->where('id', (int) $id);
        $this->ci->db->update('punjab_council_roll_number');
        return array('success' => true);
    }

    public function update_computer_no($id, $computer_no)
    {
        $this->ci->db->set('computer_no', $computer_no);
        $this->ci->db->where('id', (int) $id);
        $this->ci->db->update('punjab_council_roll_number');
        return array('success' => true);
    }

    public function delete_roll_no($id)
    {
        $this->ci->db->where('id', (int) $id)->delete('punjab_council_roll_number');
        return array('success' => true);
    }

    // --- Final result ---

    public function final_result_students($campus_id, $class_id, $user)
    {
        $this->ci->db->select('students.*, classes.name as class_name, classes.session, campuses.campus_name, courses.course_name, contractors.name as contractor_name');
        $this->ci->db->from('students');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'left');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
        $this->ci->db->join('courses', 'courses.course_id=students.course_id', 'left');
        $this->ci->db->join('contractors', 'students.contractor_id=contractors.contractor_id', 'left');
        $this->ci->db->where('students.status', '1');
        if ($campus_id) $this->ci->db->where('classes.campus_id', (int) $campus_id);
        if ($class_id) $this->ci->db->where('students.class_id', (int) $class_id);
        $this->_apply_class_scope($user);
        $this->ci->db->group_by('students.student_id');
        $this->ci->db->order_by('CAST(students.roll_no AS SIGNED)', 'ASC', false);
        $students = $this->ci->db->get()->result_array();
        foreach ($students as &$s) {
            $s['council_attempts'] = $this->ci->db->order_by('council_exam_no', 'ASC')->get_where('punjab_council_roll_number', array('cnic' => $s['cnic']))->result_array();
            $remarks = $this->ci->db->get_where('punjab_council_result_remarks', array('id' => $s['student_id']))->row_array();
            $s['manual_remarks'] = $remarks ? $remarks['remarks'] : '';
            $s['next_admission'] = $remarks ? $remarks['next_admission'] : '';
        }
        return $students;
    }

    public function save_manual_remarks($student_id, $remarks, $next_admission, $user)
    {
        $this->ci->db->where('id', (int) $student_id)->delete('punjab_council_result_remarks');
        $name = trim((isset($user['first_name']) ? $user['first_name'] : '') . ' ' . (isset($user['last_name']) ? $user['last_name'] : ''));
        $this->ci->db->insert('punjab_council_result_remarks', array(
            'id' => (int) $student_id,
            'remarks' => $remarks,
            'next_admission' => $next_admission,
            'add_by' => $name !== '' ? $name : 'Admin',
        ));
        return array('success' => true);
    }

    // --- Council fee preview ---

    public function council_fee_preview($post, $user)
    {
        $exam = (isset($post['council_exam_no']) ? $post['council_exam_no'] : null);
        $class = (isset($post['class']) ? $post['class'] : null);
        if (!$exam || !$class) return array('rows' => array(), 'sequences' => array());
        $rows = $this->ci->db->get_where('punjab_council_roll_number', array('council_exam_no' => $exam, 'class' => $class))->result_array();
        $enriched = array();
        foreach ($rows as $r) {
            $student = $this->ci->db->get_where('students', array('cnic' => $r['cnic']))->row_array();
            if (!$student) continue;
            $cls = $this->ci->db->get_where('classes', array('class_id' => $student['class_id']))->row_array();
            $campus = $cls ? $this->ci->db->get_where('campuses', array('campus_id' => $cls['campus_id']))->row_array() : null;
            if (!empty($post['campus_ids']) && is_array($post['campus_ids'])) {
                if (!$campus || !in_array((int) $campus['campus_id'], array_map('intval', $post['campus_ids']), true)) continue;
            }
            $contractor = $student['contractor_id'] ? $this->ci->db->get_where('contractors', array('contractor_id' => $student['contractor_id']))->row_array() : null;
            $enriched[] = array_merge($r, array(
                'student_id' => $student['student_id'],
                'college_roll_no' => $student['roll_no'],
                'campus_name' => $campus ? $campus['campus_name'] : '',
                'campus_id' => $campus ? $campus['campus_id'] : null,
                'campus_address' => $campus ? $campus['address'] : '',
                'contractor_name' => $contractor ? $contractor['name'] : '',
                'contractor_id' => $student['contractor_id'],
            ));
        }
        $seq_sup = !empty($post['exam_sequence_first']) ? $this->ci->db->get_where('exam_sequence', array('id' => (int) $post['exam_sequence_first']))->row_array() : null;
        $seq_ann = !empty($post['exam_sequence_second']) ? $this->ci->db->get_where('exam_sequence', array('id' => (int) $post['exam_sequence_second']))->row_array() : null;
        return array('rows' => $enriched, 'seq_supplementary' => $seq_sup, 'seq_annual' => $seq_ann);
    }

    public function manual_fee_missing_students($post)
    {
        $council_roll_no = (isset($post['council_exam_no']) ? $post['council_exam_no'] : null);
        $class = (isset($post['class']) ? $post['class'] : null);
        $campus_id = (isset($post['campus_id']) ? $post['campus_id'] : null);
        if (!$council_roll_no || !$class || !$campus_id) return array();
        $conci = $this->ci->db->get_where('punjab_council_roll_number', array('council_exam_no' => $council_roll_no, 'class' => $class))->result_array();
        if (!count($conci)) return array();
        $this->ci->db->select('students.*, classes.name as name, students.cnic as cnic, campuses.campus_name, contractors.name as contractor_name');
        $this->ci->db->from('students');
        $this->ci->db->join('classes', 'classes.class_id = students.class_id');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER');
        $this->ci->db->join('contractors', 'students.contractor_id=contractors.contractor_id', 'left');
        $this->ci->db->join('punjab_council_roll_number', 'punjab_council_roll_number.cnic = students.cnic', 'left');
        $this->ci->db->where('students.status', '1');
        $this->ci->db->where('classes.campus_id', (int) $campus_id);
        $this->ci->db->where('classes.exam_no <', (int) $council_roll_no);
        $this->ci->db->where("(punjab_council_roll_number.council_exam_no < " . (int) $council_roll_no . " OR punjab_council_roll_number.council_exam_no = '' OR punjab_council_roll_number.council_exam_no IS NULL)", null, false);
        $this->ci->db->where("(punjab_council_roll_number.class = '" . $this->ci->db->escape_str($class) . "' OR punjab_council_roll_number.class = '' OR punjab_council_roll_number.class IS NULL)", null, false);
        $this->ci->db->where("(((punjab_council_roll_number.result_remarks != 'Pass*' AND punjab_council_roll_number.result_remarks != 'Pass' AND punjab_council_roll_number.result_remarks != '') OR punjab_council_roll_number.result_remarks IS NULL) AND council_exam_no != '$council_roll_no')", null, false);
        $this->ci->db->where("(SELECT COUNT(*) FROM punjab_council_roll_number s WHERE s.cnic = punjab_council_roll_number.cnic AND s.class = '$class' AND s.council_exam_no = '$council_roll_no') = 0", null, false);
        if ((int) $class === 1) {
            $this->ci->db->where('(SELECT COUNT(*) FROM punjab_council_roll_number s WHERE s.cnic = punjab_council_roll_number.cnic AND s.class = 2) = 0', null, false);
        } else {
            $this->ci->db->where("(SELECT COUNT(*) FROM punjab_council_roll_number s WHERE s.cnic = punjab_council_roll_number.cnic AND s.class = 2 AND s.result_remarks LIKE '%Pass%') = 0", null, false);
        }
        $this->ci->db->order_by('punjab_council_roll_number.id', 'DESC');
        $this->ci->db->group_by('students.cnic');
        return $this->ci->db->get()->result_array();
    }

    public function create_council_fees($post, $user)
    {
        $this->ci->load->library('punjab_council_fee_engine');
        $engine = $this->ci->punjab_council_fee_engine;
        $engine->set_post($post);
        $engine->set_user_name(trim((isset($user['first_name']) ? $user['first_name'] : '') . ' ' . (isset($user['last_name']) ? $user['last_name'] : '')));
        $engine->run();
        return array('success' => true, 'message' => 'Council fees created');
    }

    // --- Next exam status ---

    public function next_exam_payments($campus_id, $council_exam_no, $class)
    {
        $class_label = ((int) $class === 1) ? '1st' : '2nd';
        $this->ci->db->select('payments.*, students.student_id, students.roll_no, students.cnic, students.first_name, students.last_name, students.father_name, students.mobile, students.status as student_status, classes.name as class_name, campuses.campus_name, contractors.name as contractor_name');
        $this->ci->db->from('payments');
        $this->ci->db->join('students', 'students.student_id=payments.custom_student_id OR students.student_id=payments.student_id', 'inner');
        $this->ci->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->ci->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->ci->db->join('contractors', 'students.contractor_id=contractors.contractor_id', 'left');
        $this->ci->db->like('payments.payment_comment', 'This fee for next exam # ' . $council_exam_no . ' ' . $class_label . ' Year', 'both');
        if ($campus_id) $this->ci->db->where('campuses.campus_id', (int) $campus_id);
        return $this->ci->db->get()->result_array();
    }

    // --- Status report ---

    public function status_report_rows($class, $council_exam_no)
    {
        $rows = array();
        foreach ($this->ci->db->get_where('campuses', array('status' => 1))->result_array() as $campus) {
            $cid = (int) $campus['campus_id'];
            $rows[] = array(
                'campus_id' => $cid,
                'campus_name' => $campus['campus_name'],
                'total_amount_sent' => totalAmountSendToCouncil($cid, $class, $council_exam_no),
                'admissions_sent' => admissionsSendToCouncil($cid, $class, $council_exam_no),
                'recognized_roll_nos' => recognizedRollNoReceiveFromCouncil($cid, $class, $council_exam_no),
                'unrecognized_roll_nos' => notRecognizedRollNoReceiveFromCouncil($cid, $class, $council_exam_no),
            );
        }
        return $rows;
    }

    // --- Conciliation ---

    public function conciliation_summary($post)
    {
        $exam = (isset($post['council_exam_no']) ? $post['council_exam_no'] : null);
        $class = (isset($post['class']) ? $post['class'] : null);
        $campus_id = (isset($post['campus_id']) ? $post['campus_id'] : null);
        $class_id = (isset($post['class_id']) ? $post['class_id'] : null);
        if (!$exam || !$class) return null;
        $results = $this->ci->db->get_where('punjab_council_roll_number', array('council_exam_no' => $exam, 'class' => $class))->result_array();
        $counts = array(
            'pass' => 0, 'fail' => 0, 'fail_absent' => 0, 'fail_all' => 0,
            'fail1' => 0, 'fail2' => 0, 'fail3' => 0, 'fail4' => 0, 'fail5' => 0, 'fail6' => 0,
            'last_chance' => 0, 'next_two' => 0, 'only_practical' => 0, 'only_theory' => 0,
        );
        $sample = null;
        foreach ($results as $result) {
            $student_data = $this->ci->db->select('students.*, classes.class_id, classes.name as class_name, campuses.campus_id, campuses.campus_name')
                ->from('students')
                ->join('classes', 'classes.class_id=students.class_id', 'INNER')
                ->join('campuses', 'classes.campus_id=campuses.campus_id', 'INNER')
                ->where('students.cnic', $result['cnic'])
                ->get()->row_array();
            if (!$student_data) continue;
            if ($campus_id && (int) $student_data['campus_id'] !== (int) $campus_id) continue;
            if ($class_id && (int) $student_data['class_id'] !== (int) $class_id) continue;
            if (!$sample) $sample = $student_data;
            $rm = $result['result_remarks'];
            if (strpos($rm, 'Pass') !== false) $counts['pass']++;
            elseif ($rm === 'Fail') $counts['fail']++;
            elseif (strpos($rm, 'Fail') !== false && strpos($rm, 'Absent') !== false) $counts['fail_absent']++;
            elseif (strpos($rm, 'Fail') !== false && strpos($rm, 'appear in all') !== false) $counts['fail_all']++;
            elseif (strpos($rm, 'Fail') !== false && strpos($rm, '1') !== false) $counts['fail1']++;
            elseif (strpos($rm, 'Fail') !== false && strpos($rm, '2') !== false) $counts['fail2']++;
            elseif (strpos($rm, 'Fail') !== false && strpos($rm, '3') !== false) $counts['fail3']++;
            elseif (strpos($rm, 'Fail') !== false && strpos($rm, '4') !== false) $counts['fail4']++;
            elseif (strpos($rm, 'Fail') !== false && strpos($rm, '5') !== false) $counts['fail5']++;
            elseif (strpos($rm, 'Fail') !== false && strpos($rm, '6') !== false) $counts['fail6']++;
            elseif (strpos($rm, 'Fail') !== false && strpos($rm, 'Last Chance') !== false) $counts['last_chance']++;
            elseif (strpos($rm, 'Fail') !== false && strpos($rm, 'Next Two') !== false) $counts['next_two']++;
            if (strpos($rm, 'Fail') !== false && (strpos($rm, '3') !== false || strpos($rm, '4') !== false || strpos($rm, '5') !== false || strpos($rm, '6') !== false)) {
                $counts['only_practical']++;
            } else {
                $counts['only_theory']++;
            }
        }
        return array(
            'campus_name' => $sample ? $sample['campus_name'] : '',
            'class_name' => $sample ? $sample['class_name'] : '',
            'council_exam_no' => $exam,
            'counts' => $counts,
        );
    }

    public function exam_numbers_for_class($selected_class)
    {
        $rows = $this->ci->db->get_where('exam_sequence', array(
            'class' => $selected_class,
            'course_id' => 1,
            'status' => 'Active',
        ))->result_array();
        $nums = array();
        foreach ($rows as $r) {
            if (!empty($r['first_year'])) $nums[] = (int) $r['first_year'];
        }
        return array_unique($nums);
    }

    public function council_exam_numbers()
    {
        $this->ci->db->select('council_exam_no, class, date, result_update_date');
        $this->ci->db->from('punjab_council_roll_number');
        $this->ci->db->group_by(array('council_exam_no', 'class'));
        $this->ci->db->order_by('council_exam_no', 'DESC');
        return $this->ci->db->get()->result_array();
    }

    public function classes_for_campus($campus_id)
    {
        $this->ci->db->select('class_id, name, session');
        $this->ci->db->from('classes');
        $this->ci->db->where('status', '1');
        if ($campus_id) $this->ci->db->where('campus_id', (int) $campus_id);
        $this->ci->db->order_by('class_id', 'ASC');
        return $this->ci->db->get()->result_array();
    }

    // --- Helpers ---

    private function _campuses_for_user($user)
    {
        if ($user && $user['role'] === 'Admin') {
            return $this->ci->db->order_by('campus_name', 'ASC')->get('campuses')->result_array();
        }
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        $ids = $acc && !empty($acc['campus_ids']) ? array_filter(array_map('intval', explode(',', $acc['campus_ids']))) : array();
        if (!count($ids)) return array();
        return $this->ci->db->where_in('campus_id', $ids)->order_by('campus_name', 'ASC')->get('campuses')->result_array();
    }

    private function _apply_campus_scope($user)
    {
        if (!$user || $user['role'] === 'Admin') return;
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        $ids = $acc && !empty($acc['campus_ids']) ? array_filter(array_map('intval', explode(',', $acc['campus_ids']))) : array(0);
        $this->ci->db->where_in('campuses.campus_id', $ids);
    }

    private function _apply_class_scope($user)
    {
        if (!$user || $user['role'] === 'Admin') return;
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        $ids = $acc && !empty($acc['class_ids']) ? array_filter(array_map('intval', explode(',', $acc['class_ids']))) : array(0);
        $this->ci->db->where_in('students.class_id', $ids);
    }

    private function _upload_file($field, $subdir, $required = true)
    {
        if (!is_dir(FCPATH . $subdir)) mkdir(FCPATH . $subdir, 0777, true);
        if (empty($_FILES[$field]['name'])) return $required ? false : null;
        $this->ci->load->library('upload');
        $config = array('upload_path' => FCPATH . $subdir . '/', 'allowed_types' => '*');
        $this->ci->upload->initialize($config);
        if (!$this->ci->upload->do_upload($field)) return false;
        $data = $this->ci->upload->data();
        return (isset($data['file_name']) ? $data['file_name'] : false);
    }
}
