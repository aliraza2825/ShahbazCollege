<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Council List business logic for React POS (Create List, With Fee export, Print preview).
 */
class Council_list_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('council');
        $this->ci->load->model('clas');
    }

    public function can_manage($user)
    {
        if (!$user) return false;
        if ($user['role'] === 'Admin') return true;
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        return $acc && !empty($acc['council_list_sidebar']);
    }

    public function permissions($user)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : ($this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array() ?: array());
        return array(
            'is_admin' => $is_admin,
            'create_council_list' => $is_admin || !empty($acc['create_council_list']),
            'create_council_list_with_fee' => $is_admin || !empty($acc['create_council_list_with_fee']),
            'print_council_list' => $is_admin,
        );
    }

    public function meta($user)
    {
        $campuses = $this->_campuses_for_user($user);
        $courses = $this->ci->db->order_by('course_name', 'ASC')->get_where('courses', array('status' => 'Active'))->result_array();
        return array(
            'permissions' => $this->permissions($user),
            'campuses' => $campuses,
            'courses' => $courses,
        );
    }

    public function classes_for_filter($user, $campus_id = null, $course_id = null)
    {
        $this->ci->db->select('classes.class_id, classes.name, classes.session, classes.campus_id, classes.course_id');
        $this->ci->db->from('classes');
        $this->ci->db->where('classes.status', '1');
        if ($campus_id !== null && $campus_id !== '') {
            $this->ci->db->where('classes.campus_id', (int) $campus_id);
        }
        if ($course_id !== null && $course_id !== '') {
            $this->ci->db->where('classes.course_id', (int) $course_id);
        }
        if (!$user || $user['role'] !== 'Admin') {
            $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
            $class_ids = ($acc && !empty($acc['class_ids']))
                ? array_filter(array_map('intval', explode(',', $acc['class_ids'])))
                : array(0);
            $this->ci->db->where_in('classes.class_id', $class_ids);
        }
        $this->ci->db->order_by('classes.name', 'ASC');
        return $this->ci->db->get()->result_array();
    }

    public function students_for_class($class_id)
    {
        $class_id = (int) $class_id;
        if ($class_id <= 0) return array();
        $rows = $this->ci->council->getClassStudents($class_id);
        $out = array();
        foreach ($rows as $row) {
            $row['display_name'] = $this->_student_display_name($row);
            $out[] = $row;
        }
        return $out;
    }

    public function create_list_export($class_id)
    {
        $class_id = (int) $class_id;
        if ($class_id <= 0) {
            return array('success' => false, 'message' => 'Class required');
        }
        $rows = $this->ci->council->getClassStudents($class_id);
        if (empty($rows)) {
            return array('success' => false, 'message' => 'No students found for this class');
        }
        $headers = array(
            'Sr. #', 'Student ID', 'Roll #', 'CNIC No.', 'Name & Father Name',
            'Postal Address', 'Student Mobile Number', 'Board Name', 'Institute Contact Number',
        );
        $csv_rows = array();
        $i = 1;
        foreach ($rows as $student) {
            $csv_rows[] = array(
                $i,
                isset($student['student_id']) ? $student['student_id'] : '',
                isset($student['roll_no']) ? $student['roll_no'] : '',
                isset($student['cnic']) ? $student['cnic'] : '',
                $this->_student_display_name($student, true),
                isset($student['address']) ? $student['address'] : '',
                isset($student['mobile']) ? $student['mobile'] : '',
                isset($student['board']) ? $student['board'] : '',
                isset($student['institute']) ? $student['institute'] : '03158042977',
            );
            $i++;
        }
        return array(
            'success' => true,
            'filename' => 'Shahbaz-College-Council-List-of-Students.csv',
            'headers' => $headers,
            'rows' => $csv_rows,
        );
    }

    // --- Fee export (chunked CSV, same as legacy council_list) ---

    public function start_fee_export($class_id, $course_id, $campus_id)
    {
        $class_id = trim((string) $class_id);
        $course_id = trim((string) $course_id);
        $campus_id = trim((string) $campus_id);

        $total = $this->ci->council->countCouncilFeeStudents($class_id, $course_id, $campus_id, true);
        if ($total <= 0) {
            return array('success' => false, 'message' => 'No students found for selected filters.');
        }

        $labels = $this->_resolve_fee_export_labels($class_id, $course_id, $campus_id);
        if (function_exists('random_bytes')) {
            $token = bin2hex(random_bytes(16));
        } else {
            $token = md5(uniqid((string) mt_rand(), true));
        }
        $filename = $labels['campus_name'].'_'.$labels['course_name'].'_'.$labels['class_name'].'_'.date('Y-m-d_H-i-s').'.csv';

        $directory = $this->_fee_export_directory();
        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        $csvPath = $directory . '/' . $token . '.csv';
        $fp = @fopen($csvPath, 'w');
        if (!$fp) {
            return array('success' => false, 'message' => 'Could not create export file on server.');
        }
        fputcsv($fp, $this->_fee_export_headers());
        fclose($fp);

        $state = array(
            'token' => $token,
            'status' => 'processing',
            'class_id' => $class_id,
            'course_id' => $course_id,
            'campus_id' => $campus_id,
            'total' => $total,
            'processed' => 0,
            'chunk_size' => 60,
            'file_path' => $csvPath,
            'download_name' => $filename,
            'created_at' => date('Y-m-d H:i:s'),
        );
        $this->_write_fee_export_state($token, $state);

        return array(
            'success' => true,
            'token' => $token,
            'total' => $total,
            'processed' => 0,
        );
    }

    public function process_fee_export($token)
    {
        $token = trim((string) $token);
        if ($token === '' || !preg_match('/^[a-f0-9]{32}$/', $token)) {
            return array('success' => false, 'message' => 'Invalid export token.');
        }

        $state = $this->_read_fee_export_state($token);
        if (!$state) {
            return array('success' => false, 'message' => 'Export session not found.');
        }

        if ($state['status'] === 'completed') {
            return array(
                'success' => true,
                'completed' => true,
                'processed' => (int) $state['processed'],
                'total' => (int) $state['total'],
            );
        }

        $offset = (int) $state['processed'];
        $limit = isset($state['chunk_size']) ? (int) $state['chunk_size'] : 20;
        if ($limit <= 0) $limit = 20;

        $students = $this->ci->council->getCouncilFeeStudentsChunk(
            $state['class_id'],
            $state['course_id'],
            $state['campus_id'],
            $limit,
            $offset,
            true
        );

        $fp = @fopen($state['file_path'], 'a');
        if (!$fp) {
            return array('success' => false, 'message' => 'Could not write export file.');
        }

        $maps = $this->_fee_prepare_batch_maps($students);
        foreach ($students as $student) {
            fputcsv($fp, $this->_fee_export_row($student, $maps));
        }
        fclose($fp);

        if (empty($students)) {
            $state['processed'] = (int) $state['total'];
            $state['status'] = 'completed';
            $state['completed_at'] = date('Y-m-d H:i:s');
            $this->_write_fee_export_state($token, $state);
            return array(
                'success' => true,
                'completed' => true,
                'processed' => (int) $state['processed'],
                'total' => (int) $state['total'],
            );
        }

        $state['processed'] = $offset + count($students);
        if ($state['processed'] >= (int) $state['total']) {
            $state['processed'] = (int) $state['total'];
            $state['status'] = 'completed';
            $state['completed_at'] = date('Y-m-d H:i:s');
        }
        $this->_write_fee_export_state($token, $state);

        return array(
            'success' => true,
            'completed' => ($state['status'] === 'completed'),
            'processed' => (int) $state['processed'],
            'total' => (int) $state['total'],
        );
    }

    public function fee_export_download_info($token)
    {
        $token = trim((string) $token);
        if ($token === '' || !preg_match('/^[a-f0-9]{32}$/', $token)) {
            return null;
        }
        $state = $this->_read_fee_export_state($token);
        if (!$state || !isset($state['status']) || $state['status'] !== 'completed') {
            return null;
        }
        $filePath = isset($state['file_path']) ? $state['file_path'] : '';
        if ($filePath === '' || !is_file($filePath)) {
            return null;
        }
        return array(
            'file_path' => $filePath,
            'download_name' => isset($state['download_name']) && $state['download_name'] !== ''
                ? $state['download_name']
                : 'council-fee-export.csv',
        );
    }

    // --- Private helpers ---

    private function _campuses_for_user($user)
    {
        if ($user && $user['role'] === 'Admin') {
            return $this->ci->db->order_by('campus_name', 'ASC')->get('campuses')->result_array();
        }
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        $ids = ($acc && !empty($acc['campus_ids']))
            ? array_filter(array_map('intval', explode(',', $acc['campus_ids'])))
            : array();
        if (!count($ids)) return array();
        return $this->ci->db->where_in('campus_id', $ids)->order_by('campus_name', 'ASC')->get('campuses')->result_array();
    }

    private function _student_display_name($row, $plain = false)
    {
        $first = isset($row['first_name']) ? ucfirst(strtolower($row['first_name'])) : '';
        $last = isset($row['last_name']) ? ucfirst(strtolower($row['last_name'])) : '';
        $father = isset($row['father_name']) ? ucfirst(strtolower($row['father_name'])) : '';
        $rel = (isset($row['gender']) && $row['gender'] === 'Male') ? 'S/O' : 'D/O';
        if ($plain) {
            return trim($first.' '.$last).' '.$rel.' '.$father;
        }
        return trim($first.' '.$last)."\n".$rel.' '.$father;
    }

    private function _fee_export_directory()
    {
        return FCPATH . 'downloads/council_exports';
    }

    private function _fee_export_state_path($token)
    {
        return $this->_fee_export_directory() . '/' . $token . '.json';
    }

    private function _read_fee_export_state($token)
    {
        $statePath = $this->_fee_export_state_path($token);
        if (!is_file($statePath)) return null;
        $state = json_decode((string) @file_get_contents($statePath), true);
        return is_array($state) ? $state : null;
    }

    private function _write_fee_export_state($token, $state)
    {
        @file_put_contents($this->_fee_export_state_path($token), json_encode($state));
    }

    private function _sanitize_export_label($text, $default)
    {
        $text = trim((string) $text);
        if ($text === '') return $default;
        $text = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $text);
        $text = trim($text, '-');
        return $text !== '' ? $text : $default;
    }

    private function _resolve_fee_export_labels($class_id, $course_id, $campus_id)
    {
        $class_name = 'All-Classes';
        $course_name = 'All-Courses';
        $campus_name = 'All-Campuses';

        if ($class_id !== '') {
            $classRow = $this->ci->db->get_where('classes', array('class_id' => $class_id))->row_array();
            $class_name = $this->_sanitize_export_label(isset($classRow['name']) ? $classRow['name'] : '', 'All-Classes');
        }
        if ($course_id !== '') {
            $courseRow = $this->ci->db->get_where('courses', array('course_id' => $course_id))->row_array();
            $course_name = $this->_sanitize_export_label(isset($courseRow['course_name']) ? $courseRow['course_name'] : '', 'All-Courses');
        }
        if ($campus_id !== '') {
            $campusRow = $this->ci->db->get_where('campuses', array('campus_id' => $campus_id))->row_array();
            $campus_name = $this->_sanitize_export_label(isset($campusRow['campus_name']) ? $campusRow['campus_name'] : '', 'All-Campuses');
        }

        return array(
            'class_name' => $class_name,
            'course_name' => $course_name,
            'campus_name' => $campus_name,
        );
    }

    private function _fee_export_headers()
    {
        return array(
            'Student ID', 'Roll #', 'CNIC No.', 'Name & Father Name', 'Postal Address',
            'Student Mobile Number', 'Board Name', 'Institute Contact Number', 'Total Fee',
            'Fee Decided Current Time', 'Total Fee Submitted', 'Remaining Fee Payable At Current Time',
            'Unpaid Installments AT Cuurent Time', 'Fee Detail Paid', 'Fee Detail Unpaid',
            'Percentage Fee Receive', 'Percentage Paid Installments According to Decision',
            'Renew Installments', 'Course Name', 'Cast', 'Qualification', 'Campus', 'Date of Birth',
            'Email', 'City', 'Student Card', 'Gender', 'Religion', 'Class', 'Registration Date',
            'System Registration Date', 'Blood Group', 'Books', 'Emergency Number', 'Section',
            'Student Type', 'Shift', 'Pharmacy Coucil Data', 'Document Links', 'Machine ID',
        );
    }

    private function _fee_prepare_batch_maps($students)
    {
        $maps = array(
            'total_fee' => array(), 'fee_decided' => array(), 'total_paid' => array(),
            'unpaid_current_count' => array(), 'paid_detail' => array(), 'unpaid_detail' => array(),
            'renew_count' => array(), 'documents' => array(), 'pharmacy' => array(),
        );
        if (empty($students)) return $maps;

        $studentIds = array();
        $cnics = array();
        foreach ($students as $student) {
            $studentIds[] = (int) $student['student_id'];
            if (!empty($student['cnic'])) $cnics[] = $student['cnic'];
        }
        $studentIds = array_values(array_unique(array_filter($studentIds)));
        $cnics = array_values(array_unique(array_filter($cnics)));
        if (empty($studentIds)) return $maps;

        $payments = $this->ci->db
            ->select('id, student_id, amount, actual_amount, dead_line, actual_paid_date, paid, merged_challan')
            ->where_in('student_id', $studentIds)
            ->order_by('dead_line', 'ASC')
            ->get('payments')
            ->result_array();

        $today = date('Y-m-d');
        $mergedPaidSeen = array();
        foreach ($payments as $payment) {
            $sid = (int) $payment['student_id'];
            $maps['total_fee'][$sid] = (isset($maps['total_fee'][$sid]) ? $maps['total_fee'][$sid] : 0) + (float) $payment['amount'];
            if (!empty($payment['dead_line']) && $payment['dead_line'] < $today) {
                $maps['fee_decided'][$sid] = (isset($maps['fee_decided'][$sid]) ? $maps['fee_decided'][$sid] : 0) + (float) $payment['amount'];
            }
            if ((int) $payment['paid'] === 0) {
                if (!empty($payment['dead_line']) && $payment['dead_line'] < $today) {
                    $maps['unpaid_current_count'][$sid] = (isset($maps['unpaid_current_count'][$sid]) ? $maps['unpaid_current_count'][$sid] : 0) + 1;
                }
                $maps['unpaid_detail'][$sid][] = 'Fee '.$payment['amount'].' not paid on '.$payment['dead_line'];
                continue;
            }
            $merged = isset($payment['merged_challan']) ? trim((string) $payment['merged_challan']) : '';
            if ($merged !== '') {
                $mergedKey = $sid.'|'.$merged;
                $actualAmount = (float) $payment['actual_amount'];
                if ($actualAmount <= 0 || isset($mergedPaidSeen[$mergedKey])) continue;
                $mergedPaidSeen[$mergedKey] = 1;
            }
            $maps['total_paid'][$sid] = (isset($maps['total_paid'][$sid]) ? $maps['total_paid'][$sid] : 0) + (float) $payment['actual_amount'];
            $maps['paid_detail'][$sid][] = 'Rs '.$payment['actual_amount'].' paid on '.$payment['actual_paid_date'];
        }

        $renewRows = $this->ci->db
            ->select('payments.student_id, COUNT(*) as renew_count', false)
            ->from('fees_remarks')
            ->join('payments', 'payments.id=fees_remarks.fee_id', 'inner')
            ->where_in('payments.student_id', $studentIds)
            ->group_by('payments.student_id')
            ->get()->result_array();
        foreach ($renewRows as $renewRow) {
            $maps['renew_count'][(int) $renewRow['student_id']] = (int) $renewRow['renew_count'];
        }

        $documents = $this->ci->db
            ->select('student_id, type, upload_image, online_image, image')
            ->where_in('student_id', $studentIds)
            ->get('student_documents')->result_array();
        foreach ($documents as $document) {
            $sid = (int) $document['student_id'];
            if ((int) $document['upload_image'] === 1) {
                $maps['documents'][$sid][] = $document['type'].' = '.$document['online_image'];
            } else {
                $maps['documents'][$sid][] = $document['type'].' = '.base_url().'uploads/'.$document['image'];
            }
        }

        if (!empty($cnics)) {
            $pharmacyRows = $this->ci->db
                ->select('cnic, class, council_exam_no, roll_no, date, result_remarks, result_update_date')
                ->where_in('cnic', $cnics)
                ->get('punjab_council_roll_number')->result_array();
            foreach ($pharmacyRows as $pharmacyRow) {
                $cnic = $pharmacyRow['cnic'];
                $class = ((int) $pharmacyRow['class'] === 1) ? '1st Year' : '2nd Year';
                $part = 'Class : '.$class;
                $part .= ' | Exam Number : '.$pharmacyRow['council_exam_no'];
                $part .= ' | Roll Number : '.$pharmacyRow['roll_no'];
                $part .= ' | Roll Number Upload Date : '.date('Y-m-d', strtotime($pharmacyRow['date']));
                if (!empty($pharmacyRow['result_remarks'])) {
                    $part .= ' | Result Upload Date : '.date('Y-m-d', strtotime($pharmacyRow['result_update_date']));
                    $part .= ' | Result Remarks : '.$pharmacyRow['result_remarks'];
                }
                $maps['pharmacy'][$cnic][] = $part;
            }
        }

        return $maps;
    }

    private function _fee_export_row($student, $maps = array())
    {
        $studentId = (int) $student['student_id'];
        $totalFeeAmount = isset($maps['total_fee'][$studentId]) ? (float) $maps['total_fee'][$studentId] : 0;
        array_push($student, $totalFeeAmount);

        $feeDecidedAmount = isset($maps['fee_decided'][$studentId]) ? (float) $maps['fee_decided'][$studentId] : 0;
        array_push($student, $feeDecidedAmount);

        $totalStudentPaidFee = isset($maps['total_paid'][$studentId]) ? (float) $maps['total_paid'][$studentId] : 0;
        array_push($student, $totalStudentPaidFee);
        array_push($student, $feeDecidedAmount - $totalStudentPaidFee);
        array_push($student, isset($maps['unpaid_current_count'][$studentId]) ? (int) $maps['unpaid_current_count'][$studentId] : 0);
        array_push($student, !empty($maps['paid_detail'][$studentId]) ? implode(' | ', $maps['paid_detail'][$studentId]).' | ' : '');
        array_push($student, !empty($maps['unpaid_detail'][$studentId]) ? implode(' | ', $maps['unpaid_detail'][$studentId]).' | ' : '');

        if ($totalStudentPaidFee == 0 || $totalFeeAmount == 0) {
            array_push($student, 'N/A');
        } else {
            array_push($student, round($totalStudentPaidFee / $totalFeeAmount * 100, 2));
        }

        if ($totalStudentPaidFee == 0 || $feeDecidedAmount == 0) {
            array_push($student, 'N/A');
        } else {
            array_push($student, round($totalStudentPaidFee / $feeDecidedAmount * 100, 2));
        }

        array_push($student, isset($maps['renew_count'][$studentId]) ? (int) $maps['renew_count'][$studentId] : 0);
        array_push($student, isset($student['export_course_name']) ? $student['export_course_name'] : '');
        array_push($student, isset($student['caste']) ? $student['caste'] : '');
        array_push($student, isset($student['qualification']) ? $student['qualification'] : '');
        array_push($student, isset($student['export_campus_name']) ? $student['export_campus_name'] : '');
        array_push($student, isset($student['date_of_birth']) ? $student['date_of_birth'] : '');
        array_push($student, isset($student['email']) ? $student['email'] : '');
        array_push($student, isset($student['city']) ? $student['city'] : '');

        $student_card = (isset($student['student_card']) && $student['student_card'] == 1) ? 'Yes' : 'No';
        array_push($student, $student_card);
        array_push($student, isset($student['gender']) ? $student['gender'] : '');
        array_push($student, isset($student['religion']) ? $student['religion'] : '');
        array_push($student, isset($student['export_class_name']) ? $student['export_class_name'] : '');
        array_push($student, isset($student['registration_date']) ? $student['registration_date'] : '');
        array_push($student, isset($student['entry_date']) ? $student['entry_date'] : '');
        array_push($student, isset($student['blood_group']) ? $student['blood_group'] : '');

        $book_1 = (isset($student['books_1']) && $student['books_1'] == 1) ? '1st Year Book : Taken' : '1st Year Book : Not Taken';
        $book_2 = (isset($student['books_2']) && $student['books_2'] == 1) ? '2nd Year Book : Taken' : '2nd Year Book : Not Taken';
        array_push($student, $book_1.' '.$book_2);
        array_push($student, isset($student['emergency_no']) ? $student['emergency_no'] : '');
        array_push($student, isset($student['section']) ? $student['section'] : '');
        array_push($student, isset($student['study_type']) ? $student['study_type'] : '');
        array_push($student, isset($student['shift']) ? $student['shift'] : '');

        $cnic = isset($student['cnic']) ? $student['cnic'] : '';
        array_push($student, !empty($maps['pharmacy'][$cnic]) ? implode(' --- ', $maps['pharmacy'][$cnic]).' --- ' : '');
        array_push($student, !empty($maps['documents'][$studentId]) ? implode(' | ', $maps['documents'][$studentId]).' | ' : '');
        array_push($student, isset($student['export_machine_id']) ? $student['export_machine_id'] : '');

        return array_values($student);
    }
}
