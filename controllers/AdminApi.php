<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class AdminApi extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model("Object");
        $this->load->model('student');
        $this->load->library('upload');
        $this->load->model('account');
        $this->ensure_staff_timing_columns();
        require_once("vendor/autoload.php");
    }

    private function ensure_staff_timing_columns()
    {
        if ($this->db->table_exists('staff_timing') && !$this->db->field_exists('staff_type_id', 'staff_timing')) {
            $this->db->query("ALTER TABLE staff_timing ADD staff_type_id INT NULL AFTER staff_id");
        }
    }

    private function get_staff_day_timing($user_id, $day)
    {
        $timing = $this->db
            ->where('staff_id', $user_id)
            ->where('day', $day)
            ->get('staff_timing')
            ->row_array();

        if ($timing) {
            return $timing;
        }

        if (!$this->db->field_exists('staff_type_id', 'staff_timing')) {
            return array();
        }

        $user = $this->db
            ->select('staff_type_id')
            ->where('user_id', $user_id)
            ->get('users')
            ->row_array();

        $staffTypeId = isset($user['staff_type_id']) ? (int) $user['staff_type_id'] : 0;
        if ($staffTypeId <= 0) {
            return array();
        }

        return $this->db
            ->where('staff_type_id', $staffTypeId)
            ->where('day', $day)
            ->get('staff_timing')
            ->row_array();
    }

    private function is_off_day_timing($timing)
    {
        if (empty($timing)) {
            return false;
        }

        $checkinTiming = isset($timing['checkin_timing']) ? trim((string) $timing['checkin_timing']) : '';
        $checkoutTiming = isset($timing['checkout_timing']) ? trim((string) $timing['checkout_timing']) : '';

        if (strtoupper($checkinTiming) === 'OFF' || strtoupper($checkoutTiming) === 'OFF') {
            return true;
        }

        return $checkinTiming === '00:00:00' || $checkoutTiming === '00:00:00';
    }

    public function Login(){
        $username = $this->input->post('username');
        $password = md5($this->input->post('password'));
        $device_id = $this->input->post('device_id');
        $app_version = $this->input->post('app_version');

        $this->db->select('*');
        $this->db->from('users');
        $this->db->where(array('username'=>$username, 'password'=>$password, 'status'=>'1'));
        $query = $this->db->get()->result_array();
        $today = date('Y-m-d');
        $first_day = date('Y-m-01');

        if(count($query)>0){

            $this->db->set(array(
                'device_id'=>$device_id,
                'app_version'=>$app_version,
            ));
            $this->db->where('user_id',$query[0]["user_id"]);
            $this->db->update('users');

            $access = $this->db->get_where('access', array('user_id'=>$query[0]["user_id"]))->result_array();
            $user_id = $query[0]["user_id"];
            $cash = $this->db->get_where("petty_cash_college_wise","assign_to = '$user_id'")->result_array();
            if (count($cash)>0)
                $petty = pettycash_statement($cash[0]['id']);
            else
                $petty = 0;

            $exp_today = $this->db->select('sum(amount) as total_expense_today')->get_where("expenses",array("actual_date >=" => $today." 00:00:00","actual_date <="=>$today." 23:59:59"))->row()->total_expense_today;
            $exp_month = $this->db->select('sum(amount) as total_expense_month')->get_where("expenses",array("actual_date >=" => $first_day." 00:00:00","actual_date <="=>$today." 23:59:59"))->row()->total_expense_month;

            $this->db->select('sum(actual_amount) as total_amount');
            $this->db->from('payments');
            $this->db->where(array("payments.actual_paid_date >=" => $today." 00:00:00","payments.actual_paid_date <=" => $today." 23:59:59"));
            $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
            $this->db->group_by("CASE WHEN merged_challan IS NOT NULL AND PAID = 1 THEN merged_challan else '' end",false);
            $querytoday = $this->db->get()->row();

            $this->db->select('sum(actual_amount) as total_amount');
            $this->db->from('payments');
            $this->db->where(array("payments.actual_paid_date >=" => $today." 00:00:00","payments.actual_paid_date <=" => $today." 23:59:59"));
            $this->db->where('merged_challan is null and paid = 1');
            $querytoday2 = $this->db->get()->row();

            $today_recovery = 0;
            if ($querytoday!= null)
                $total_recovery_today = $today_recovery+$querytoday->total_amount;
            if ($querytoday2!= null)
                $total_recovery_today = $today_recovery+$querytoday2->total_amount;

            $this->db->select('sum(actual_amount) as total_amount');
            $this->db->from('payments');
            $this->db->where(array("payments.actual_paid_date >=" => $first_day." 00:00:00","payments.actual_paid_date <=" => $today." 23:59:59"));
            $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
            $this->db->group_by("CASE WHEN merged_challan IS NOT NULL AND PAID = 1 THEN merged_challan else '' end",false);
            $querymonth = $this->db->get()->row();

            $this->db->select('sum(actual_amount) as total_amount');
            $this->db->from('payments');
            $this->db->where(array("payments.actual_paid_date >=" => $first_day." 00:00:00","payments.actual_paid_date <=" => $today." 23:59:59"));
            $this->db->where('merged_challan is null and paid = 1');
            $querymonth2 = $this->db->get()->row();

            $month_recovery = 0;
            if ($querymonth!= null)
                $total_recovery_month = $month_recovery+$querymonth->total_amount;
            if ($querytoday2!= null)
                $total_recovery_month = $month_recovery+$querymonth2->total_amount;

            if ($exp_today == "")
                $exp_today = 0;

            $designations = $this->db->where_in("designation_id",explode(",",$query[0]["designation_id"]))->get("designations")->result_array();

            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Login Successful',
                'login_response'=>$query,
                'user_access'=>$access,
                'petty_cash'=>$petty,
                'recovery_today'=>"$total_recovery_today",
                'recovery_month'=>"$total_recovery_month",
                'expense_today'=>"$exp_today",
                'response_designations'=>$designations,
                'expense_month'=>"$exp_month"
            );
            echo json_encode($result);
        }
        else{
            $arr = array();
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'Login Failed',
                'response_designations'=>array(),
                'login_response'=>$arr
            );
            echo json_encode($result);
        }
    }

    public function GetAllStudents(){

        $qry = "SELECT 
                students.student_id, 
                students.course_id, 
                students.device_id, 
                students.study_campus, 
                students.first_name, 
                students.last_name, 
                students.father_name,
                students.roll_no, 
                students.plan_id, 
                students.gender,
                students.qualification, 
                students.caste, 
                students.religion, 
                students.email, 
                students.cnic, 
                students.blood_group, 
                students.date_of_birth, 
                students.registration_date, 
                students.total_fee, 
                students.city, 
                students.address, 
                students.mobile, 
                students.emergency_no, 
                students.class_id, 
                students.status,
                students.contractor_id, 
                students.contract_id,
                students.books_1, 
                students.books_2,
                students.student_card, 
                students.notes, 
                students.supply, 
                students.board, 
                students.add_by, 
                students.last_edit, 
                students.section, 
                students.shift, 
                students.study_type, 
                students.clear_status, 
                students.clear_by, 
                students.passcode, 
                students.passcode_date, 
                students.entry_date, 
                students.district, 
                students.tehsil, 
                students.mark_of_identification, 
                students.place_of_birth,
                courses.course_name,
                courses.course_type,
                courses.course_duration_year,
                courses.course_duration_month,
                campuses.campus_name,
                campuses.campus_code,
                classes.name,
                classes.seats,
                classes.session,
                study_type.name as study_type_name,
                shifts.name as shift_name
                FROM students
                INNER JOIN courses ON courses.course_id = students.course_id
                INNER JOIN campuses ON campuses.campus_id = students.study_campus
                INNER JOIN classes ON classes.class_id = students.class_id
                LEFT JOIN study_type ON study_type.id = students.study_type
                LEFT JOIN shifts ON shifts.id = students.shift
                WHERE students.status = 1 
                ORDER BY `students`.`student_id`  DESC";
        $query = $this->db->query($qry)->result_array();


        if(count($query)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Login Successful',
                'students_response'=>$query
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'Login Failed',
                'students_response'=>'null'
            );
            echo json_encode($result);
        }
    }

    public function AssignChat(){

        $user_id = $this->input->post('user_id');
        $chat_id = $this->input->post('chat_id');

        $this->db->set(array(
            'chat_status'=>'1',
            'assign_id'=>$user_id
        ));

        $this->db->where('chat_id',$chat_id);
        $results=$this->db->update('chats');


        if($results){

// 		$this->db->select(' device_id ');
// 		$this->db->from('admission_applications');
// 		$this->db->where(array('application_id'=>$application_id));
// 		$device_array = $this->db->get()->fetch_array();
            $qry = "SELECT device_id FROM `users` WHERE user_id = '".$user_id."'";
            $device_array = $this->db->query($qry)->result_array();

            //echo '<pre>'; print_r($device_array); echo '</pre>';

            $url = 'https://fcm.googleapis.com/fcm/send';

            $api_key = 'AAAAFPFaubY:APA91bGaMDgdgA3O8XG_QA6ZJBBPJ_p-eLFW4AgS_S4wDm8-zQWVCy1B_G6fvkci5DOP-06seAlv1fU-DLsX5MujC7Rce0diZWq1GbrU5c0GiCt0rDIqFFX9MuiNpRIAHZ62__sbRhmP';

            $fields = array (
                'to'        => $device_array[0]["device_id"],
                // 'registration_ids' => array (
                //         $device_array
                // ),
                'data' => array (
                    "title" => "Chat Assigned",
                    "message" => "A chat is assigned to you. Please review it as soon as possible"
                )
            );


            //header includes Content type and api key
            $headers = array(
                'Content-Type:application/json',
                'Authorization:key='.$api_key
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
            // echo $result;

            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Chat Successfully Updated',
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'An error occured',
            );
            echo json_encode($result);
        }

    }

    public function AssignComplaint(){

        $user_id = $this->input->post('user_id');
        $complaint_id = $this->input->post('complaint_id');

        $this->db->set(array(
            'complaints_status'=>'1',
            'assign_id'=>$user_id
        ));

        $this->db->where('complaint_id',$complaint_id);
        $results=$this->db->update('complaints');


        if($results){

// 		$this->db->select(' device_id ');
// 		$this->db->from('admission_applications');
// 		$this->db->where(array('application_id'=>$application_id));
// 		$device_array = $this->db->get()->fetch_array();
            $qry = "SELECT device_id FROM `users` WHERE user_id = '".$user_id."'";
            $device_array = $this->db->query($qry)->result_array();

            //echo '<pre>'; print_r($device_array); echo '</pre>';

            $url = 'https://fcm.googleapis.com/fcm/send';

            $api_key = 'AAAAFPFaubY:APA91bGaMDgdgA3O8XG_QA6ZJBBPJ_p-eLFW4AgS_S4wDm8-zQWVCy1B_G6fvkci5DOP-06seAlv1fU-DLsX5MujC7Rce0diZWq1GbrU5c0GiCt0rDIqFFX9MuiNpRIAHZ62__sbRhmP';

            $fields = array (
                'to'        => $device_array[0]["device_id"],
                // 'registration_ids' => array (
                //         $device_array
                // ),
                'data' => array (
                    "title" => "Chat Assigned",
                    "message" => "A chat is assigned to you. Please review it as soon as possible"
                )
            );


            //header includes Content type and api key
            $headers = array(
                'Content-Type:application/json',
                'Authorization:key='.$api_key
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
            // echo $result;

            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Chat Successfully Updated',
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'An error occured',
            );
            echo json_encode($result);
        }

    }

    public function ReplyComplaint(){

        $student_id = $this->input->post('student_id');
        $complaint_id = $this->input->post('complaint_id');
        $reply = $this->input->post('reply');
        $status = $this->input->post('status');

        $this->db->set(array(
            'complaints_status'=>$status,
            'reply'=>$reply
        ));

        $this->db->where('complaint_id',$complaint_id);
        $results=$this->db->update('complaints');


        if($results){

// 		$this->db->select(' device_id ');
// 		$this->db->from('admission_applications');
// 		$this->db->where(array('application_id'=>$application_id));
// 		$device_array = $this->db->get()->fetch_array();
            $qry = "SELECT device_id FROM `students` WHERE student_id = '".$student_id."'";
            $device_array = $this->db->query($qry)->result_array();

            //echo '<pre>'; print_r($device_array); echo '</pre>';

            $url = 'https://fcm.googleapis.com/fcm/send';

            $api_key = 'AAAAFPFaubY:APA91bGaMDgdgA3O8XG_QA6ZJBBPJ_p-eLFW4AgS_S4wDm8-zQWVCy1B_G6fvkci5DOP-06seAlv1fU-DLsX5MujC7Rce0diZWq1GbrU5c0GiCt0rDIqFFX9MuiNpRIAHZ62__sbRhmP';

            $fields = array (
                'to'        => $device_array[0]["device_id"],
                // 'registration_ids' => array (
                //         $device_array
                // ),
                'data' => array (
                    "title" => "Chat Assigned",
                    "message" => "A chat is assigned to you. Please review it as soon as possible"
                )
            );


            //header includes Content type and api key
            $headers = array(
                'Content-Type:application/json',
                'Authorization:key='.$api_key
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
            // echo $result;

            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Chat Successfully Updated',
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'An error occured',
            );
            echo json_encode($result);
        }

    }

    public function ReviewApplication(){
        $application_id = $this->input->post('application_id');
        $status = $this->input->post('status');
        $application_notes = $this->input->post('application_notes');
        $class_id = $this->input->post('class_id');

        if ($status==2){
            $this->db->set(array(
                'status'=>$status,
                'application_notes'=>$application_notes,
                'class_id'=>$class_id
            ));
        } else {
            $this->db->set(array(
                'status'=>$status,
            ));
        }

        $this->db->where('application_id',$application_id);
        $results=$this->db->update('admission_applications');

        if($results){

// 		$this->db->select(' device_id ');
// 		$this->db->from('admission_applications');
// 		$this->db->where(array('application_id'=>$application_id));
// 		$device_array = $this->db->get()->fetch_array();
            $qry = "SELECT device_id FROM `admission_applications` WHERE application_id = '".$application_id."'";
            $device_array = $this->db->query($qry)->result_array();

            //echo '<pre>'; print_r($device_array); echo '</pre>';

            $url = 'https://fcm.googleapis.com/fcm/send';

            $api_key = 'AAAAFPFaubY:APA91bGaMDgdgA3O8XG_QA6ZJBBPJ_p-eLFW4AgS_S4wDm8-zQWVCy1B_G6fvkci5DOP-06seAlv1fU-DLsX5MujC7Rce0diZWq1GbrU5c0GiCt0rDIqFFX9MuiNpRIAHZ62__sbRhmP';

            if ($status==2){
                $fields = array (
                    'to'        => $device_array[0]["device_id"],
                    // 'registration_ids' => array (
                    //         $device_array
                    // ),
                    'data' => array (
                        "title" => "Admission Rejected",
                        "message" => "Your admission application rejected. Please review your application and you can resubmit your application"
                    )
                );
            } else if ($status==3){
                $fields = array (
                    'to'        => $device_array[0]["device_id"],
                    // 'registration_ids' => array (
                    //         $device_array
                    // ),
                    'data' => array (
                        "title" => "Admission Successfully",
                        "message" => "Your admission application successfully approved. You can now pay your registration fees to continue"
                    )
                );
            }

            //header includes Content type and api key
            $headers = array(
                'Content-Type:application/json',
                'Authorization:key='.$api_key
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            if ($result === FALSE) {
                die('FCM Send Error: ' . curl_error($ch));
            }
            curl_close($ch);
            // echo $result;

            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Application Successfully Updated',
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'An error occured',
            );
            echo json_encode($result);
        }
    }

    public function GetClasses(){

        $campus_id = $this->input->post('campus_id');
        $course_id = $this->input->post('course_id');

        $qry = "SELECT * FROM `classes` WHERE campus_id = '".$campus_id."' AND course_id = '".$course_id."' AND session = '2021-2023'";
        $query = $this->db->query($qry)->result_array();

        if(count($query)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
                'classes_response'=>$query
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'classes_response'=>'null'
            );
            echo json_encode($result);
        }

    }

    public function GetTeacherClasses(){
        $teacher = $this->input->post('teacher');

        $qry = "SELECT
temptable.id AS id,
temptable.lecture_name AS lecture_name,
temptable.course AS course,
courses.course_name AS course_name,
temptable.class AS class,
temptable.subject AS subject,
GROUP_CONCAT(temptable.subject) AS subject_ids,
temptable.campus AS campus,
temptable.shift AS shift,
temptable.session AS session,
temptable.studytype AS study_type,
temptable.room AS room,
temptable.days AS days,
temptable.teacher AS teacher,
temptable.start_date AS start_date,
temptable.end_date AS end_date,
subjects.name AS name,
campuses.campus_name, 
shifts.name as shift_name, 
study_type.name as study_type_name, 
classes.name as class_name,
GROUP_CONCAT(subjects.name) AS subject_names,
users.first_name AS first_name,
users.last_name AS last_name 
from ((((select numbers.n AS n,lectures.id AS id,lectures.lecture_name AS lecture_name,lectures.course AS course,lectures.class AS class,lectures.session AS session,lectures.campus AS campus,lectures.subjects AS subjects,lectures.shift AS shift,lectures.studytype AS studytype,lectures.room AS room,lectures.days AS days,lectures.second_teacher AS second_teacher,lectures.teacher AS teacher,lectures.start_date AS start_date,lectures.end_date AS end_date,lectures.created_by AS created_by,lectures.created_at AS created_at,substring_index(substring_index(lectures.subjects,',',numbers.n),',',-1) AS subject from ((select 1 AS n union all select 2 AS '2' union all select 3 AS '3' union all select 4 AS '4' union all select 5 AS '5') numbers join lectures on(char_length(lectures.subjects) - char_length(replace(lectures.subjects,',','')) >= numbers.n - 1)) order by lectures.id,numbers.n) temptable join subjects on(subjects.subject_id = temptable.subject)) join users on(users.user_id = temptable.teacher)) join courses on(courses.course_id = temptable.course))
INNER JOIN campuses ON campuses.campus_id = temptable.campus
                    INNER JOIN shifts ON shifts.id = temptable.shift
                    INNER JOIN study_type ON study_type.id = temptable.studytype
                    INNER JOIN classes ON classes.class_id = temptable.class
WHERE temptable.teacher = '".$teacher."' GROUP BY temptable.id";
        $query = $this->db->query($qry)->result_array();

// 		$this->db->select('*');
// 		$this->db->from('timetable');
// 		$this->db->where(array('teacher'=>$teacher));
// 		$query = $this->db->get()->result_array();

        if(count($query)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
                'timetable_response'=>$query
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'timetable_response'=>'null'
            );
            echo json_encode($result);
        }
    }

    public function GetUserRecoverySummary(){
        $user_id = $this->input->post('user_id');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $session = $this->input->post('session');
        $is_session = $this->input->post('is_session');

        //GET USER DETAILS
        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('campuses','campuses.campus_id=users.campus_id','INNER');
        $this->db->join('departments','departments.department_id=users.department_id','INNER');
        $this->db->where('users.user_id',$user_id);
        $user = $this->db->get()->result_array();
        $desigations = explode(",",$user[0]['designation_id']);
        foreach ($desigations as $desigation) {
            $recovery_management = @$this->db->get_where('recovery_management',array('designation_id'=>$desigation))->row()->recovery_management_id;
            if($recovery_management){
                $recovery_management_id = $recovery_management;
            }
        }
        $recovery = $this->db->get_where('recovery_management',array('recovery_management_id'=>$recovery_management_id))->result_array();
        $campus_ids = explode(',',$recovery[0]['campus_ids']);
        $course_ids = explode(',',$recovery[0]['course_id']);

        //GET ALL UNPAID FEE PAYMENTS DETAILS OF STUDENTS
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.dead_line<='=>$end_date,'payments.paid'=>0,'students.status'=>1));
        if ($is_session=='true'){
            $this->db->where(array('students.class_id'=>$session));
        }
        $unpaid_payments_students = $this->db->get()->result_array();

        //GET ALL UNPAID FEE PAYMENTS DETAILS OF CONTRACTS
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.dead_line<='=>$end_date,'payments.paid'=>0,'payments.payment_plan Not Like'=>'extra fee','payments.amount !='=>'4500'));
        $unpaid_payments_contracts = $this->db->get()->result_array();

        //GET ALL UNPAID STUDENTS COUNT
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.dead_line<='=>$end_date,'payments.paid'=>0,'payments.payment_plan Not like'=>'extra fee','students.status'=>1));
        if ($is_session=='true'){
            $this->db->where(array('students.class_id'=>$session));
        }
        $this->db->group_by('students.student_id');
        $fee_dues_students_count = $this->db->get()->result_array();

        //GET ALL UNPAID COUNT CONTRACTS
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.dead_line<='=>$end_date,'payments.paid'=>0,'payments.payment_plan Not Like'=>'extra fee','payments.amount !='=>'4500'));

        $this->db->group_by('contracts.contract_id');
        $fee_dues_contractors_count = $this->db->get()->result_array();


        //GET FEE PAYMENTS DETAILS OF STUDENTS
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$start_date,'payments.actual_paid_date<='=>$end_date,'payments.paid'=>1,'students.status'=>1));
        if ($is_session=='true'){
            $this->db->where(array('students.class_id'=>$session));
        }
        $paid_payments_students = $this->db->get()->result_array();


        //GET PAID PAYMENTS COUNT OF STUDENTS
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$start_date,'payments.actual_paid_date<='=>$end_date,'payments.paid'=>1,'students.status'=>1));
        if ($is_session=='true'){
            $this->db->where(array('students.class_id'=>$session));
        }
        $this->db->group_by('students.student_id');
        $paid_count_students = $this->db->get()->result_array();


        //GET FEE PAYMENTS DETAILS OF CONTRACTS
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$start_date,'payments.actual_paid_date<='=>$end_date,'payments.paid'=>1,'payments.amount !='=>'4500'));
        $paid_payments_contracts = $this->db->get()->result_array();


        //GET FEE PAID CONTRACTORS COUNT
        $this->db->select('payments.*');
        $this->db->from('payments');
        $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$start_date,'payments.actual_paid_date<='=>$end_date,'payments.paid'=>1,'payments.amount !='=>'4500'));
        $this->db->group_by('contracts.contract_id');
        $paid_count_contracts = $this->db->get()->result_array();


        //GET SHIFTED PAYMENTS DETAILS OF STUDENTS
        $this->db->select('update_payment_requests.*');
        $this->db->from('update_payment_requests');
        $this->db->join('students','students.student_id=update_payment_requests.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('update_payment_requests.update_date>='=>$start_date,'update_payment_requests.update_date<='=>$end_date,'update_payment_requests.ok_by_admin'=>1,'update_payment_requests.amount !='=>'4500'));
        if ($is_session=='true'){
            $this->db->where(array('students.class_id'=>$session));
        }
        $shifted_payments_students = $this->db->get()->result_array();

        //GET SHIFTED PAYMENTS DETAILS OF CONTRACTS
        $this->db->select('update_payment_requests.*');
        $this->db->from('update_payment_requests');
        $this->db->join('contracts','contracts.contract_id=update_payment_requests.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('update_payment_requests.update_date>='=>$start_date,'update_payment_requests.update_date<='=>$end_date,'update_payment_requests.ok_by_admin'=>1,'update_payment_requests.amount !='=>'4500'));
        $shifted_payments_contracts = $this->db->get()->result_array();

        $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.paid_challans, payments.merged_challan,payments.challan_no, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where('payments.merged_challan IS NOT NULL and payments.actual_amount > 0');
        $this->db->where(array('payments.actual_paid_date>='=>$start_date,'payments.actual_paid_date<='=>$end_date,'payments.paid'=>1,'students.status'=>1));
        if ($is_session=='true'){
            $this->db->where(array('students.class_id'=>$session));
        }
        $this->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan else '' end",false);
        $datafine_students = $this->db->get()->result_array();

        $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.merged_challan,payments.challan_no, payments.paid_challans, payments.dead_line, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$start_date,'payments.actual_paid_date<='=>$end_date,'payments.paid'=>1,'students.status'=>1));
        $this->db->where('payments.merged_challan is null');
        if ($is_session=='true'){
            $this->db->where(array('students.class_id'=>$session));
        }
        $this->db->or_where('merged_challan IS not NULL and actual_amount = 0');
        $datapaid_payments_fine_students = $this->db->get()->result_array();

        $fine_students = array_merge($datafine_students,$datapaid_payments_fine_students);

        // echo print_r($unpaid_payments_students);
        // echo print_r($unpaid_payments_contracts);
        // echo print_r($fee_dues_students_count);
        // echo print_r($fee_dues_contractors_count);
        // echo print_r($paid_payments_students);
        // echo print_r($paid_count_students);
        // echo print_r($paid_payments_contracts);
        // echo print_r($paid_count_contracts);
        // echo print_r($shifted_payments_students);
        // echo print_r($shifted_payments_contracts);
        // echo print_r($datafine_students);
        // echo print_r($datapaid_payments_fine_students);
        // echo print_r($fine_students);

        $rules = $this->db->get_where('recovery_management_rules',array('recovery_management_id'=>$recovery_management_id))->result_array();
        $comission = $this->db->get_where('recovery_management',array('recovery_management_id'=>$recovery_management_id))->result_array();
        $total_entries = (count($unpaid_payments_students)+count($unpaid_payments_contracts)+count($paid_payments_students)+count($paid_payments_contracts)+count($shifted_payments_students)+count($shifted_payments_contracts));

        $total_entries_in_percent = $total_entries/100;
        $paid_entries = count($paid_payments_students)+count($paid_payments_contracts);

        if($total_entries>0)
        {
            $submitted_fee_percentage = round(($paid_entries/$total_entries)*100,2);

        }
        else
        {
            $submitted_fee_percentage=0;

        }

        $this->db->order_by('start','ASC');
        //$this->db->limit(1);
        $comission_rule = $this->db->get_where('recovery_management_rules',array('recovery_management_id'=>$recovery_management_id,'start<='=>$submitted_fee_percentage,'end>'=>$submitted_fee_percentage))->result_array();

        if(count($comission_rule)>0)
        {
            $installment_comission =  $comission_rule[0]['comission'];

        }
        else
        {
            $installment_comission=0;

        }

        $total_paidunpaid_students = count($fee_dues_students_count)+count($fee_dues_contractors_count)+count($paid_count_students)+count($paid_count_contracts);

        $delete_entries=0;
        $shifted_entries=0;
        foreach($shifted_payments_students as $shifted_payment)
        {
            if($shifted_payment['del']==1)
            {
                $delete_entries++;
            }
            else
            {
                $shifted_entries++;
            }
        }
        foreach($shifted_payments_contracts as $shifted_payment)
        {
            if($shifted_payment['del']==1)
            {
                $delete_entries++;
            }
            else
            {
                $shifted_entries++;
            }
        }

        $paid_entries = count($paid_payments_students)+count($paid_payments_contracts);
        $paid_students = count($paid_count_students)+count($paid_count_contracts);
        $unpaid_entries = count($unpaid_payments_students)+count($unpaid_payments_contracts);
        $unpaid_students = $total_paidunpaid_students - (count($paid_count_students)+count($paid_count_contracts));

        $unpaid_splited_entries = 0;
        foreach($unpaid_payments_students  as $unpaid_payments_student)
        {
            if($unpaid_payments_student['split']>0)
            {
                $unpaid_splited_entries++;
            }
        }
        foreach($unpaid_payments_contracts  as $unpaid_payments_contract)
        {
            if($unpaid_payments_contract['split']>0)
            {
                $unpaid_splited_entries++;
            }
        }

        $paid_splited_entries = 0;
        foreach($paid_payments_students  as $paid_payments_student)
        {
            if($paid_payments_student['split']>0)
            {
                $paid_splited_entries++;
            }
        }
        foreach($paid_payments_contracts  as $paid_payments_contract)
        {
            if($paid_payments_contract['split']>0)
            {
                $paid_splited_entries++;
            }
        }

        if($total_entries>0)
        {
            //DELETED ENTRIES NOT INCLUDED
            $submitted_fee_percentage = round(($paid_entries/($total_entries-$delete_entries))*100,2);
        }
        else
        {
            $submitted_fee_percentage=0;
        }

        $this->db->order_by('start','ASC');
        //$this->db->limit(1);
        $comission_rule = $this->db->get_where('recovery_management_rules',array('recovery_management_id'=>$recovery_management_id,'start<='=>$submitted_fee_percentage,'end>'=>$submitted_fee_percentage))->result_array();
        if(count($comission_rule)>0)
        {
            $amount=0;
            $percent_amount=$comission_rule[0]['comission'];
            foreach($paid_payments_students as $due)
            {
                if($due['split'] == '1' ){

                    $amount+=0.5*$percent_amount;
                } else if($due['split'] == '2' ){

                    $amount+=0.25*$percent_amount;
                }else{
                    $amount+=$percent_amount;
                }
            }
            foreach($paid_payments_contracts as $due)
            {
                if($due['split'] == '1' ){

                    $amount+=0.5*$percent_amount;
                } else if($due['split'] == '2' ){

                    $amount+=0.25*$percent_amount;
                }else{
                    $amount+=$percent_amount;
                }
            }


            $installment_comission =  $amount;
        }
        else
        {
            $installment_comission=0;
        }

        $collected_fine=0;
        foreach($fine_students as $paid_payments_student)
        {
            if($paid_payments_student['paid']='1')
            {
                $collected_fine += $paid_payments_student['fine_amount'];
            }
        }

        if(count($comission)>0)
        {
            $min_fine_amount = $comission[0]['min_fine_amount'];
            $fine_amount_percentage = $comission[0]['fine_amount_percentage'];
            if($collected_fine>$min_fine_amount)
            {
                $fine_comission =  ($collected_fine*$fine_amount_percentage)/100;
            }
            else
            {
                $fine_comission = 0;
            }

        }
        else
        {
            $fine_comission = 0;
        }

        $insentive = $installment_comission+$fine_comission;
        $collected_fine = 'Rs '.$collected_fine;
        $fine_comission = 'Rs '.$fine_comission;

        $data = array();

        $object = new Object();
        $object->total_enteries = $total_entries-$delete_entries;
        $object->total_students = $total_paidunpaid_students;
        $object->total_fee_shifted = $shifted_entries;
        $object->total_fee_deleted = $delete_entries;
        $object->total_paid_enteries = $paid_entries;
        $object->total_paid_students = $paid_students;
        $object->total_unpaid_enteries = $unpaid_entries;
        $object->total_unpaid_students = $unpaid_students;
        $object->fee_recovered_percentage = $submitted_fee_percentage;
        $object->calculated_insentive_of_installment = $installment_comission;
        $object->collected_fine_amount = $collected_fine;
        $object->calculated_insentive_of_fine = $fine_comission;
        $object->total_insentive = 'Rs. '.$insentive;
        array_push($data, $object);

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Found',
            'recovery_summary_report'=>$data
        );
        echo json_encode($result);

    }

    public function GetRecoverySessions(){

        $user_id = $this->input->post("user_id");
        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('campuses','campuses.campus_id=users.campus_id','INNER');
        $this->db->join('departments','departments.department_id=users.department_id','INNER');
        $this->db->where('users.user_id',$user_id);
        $user = $this->db->get()->result_array();

        $desigations = explode(",",$user[0]['designation_id']);
        foreach ($desigations as $desigation) {
            $recovery_management = @$this->db->get_where('recovery_management',array('designation_id'=>$desigation))->row();
            if($recovery_management){
                $recovery_management_id = $recovery_management;
            }
        }
        $campus_ids = explode(',',$recovery_management_id->campus_ids);
        $course_ids = explode(',',$recovery_management_id->course_id);

        // $query = $this->db->select('class_id, name, session')
        //     ->from("classes")
        //     ->where_in("campus_id",$campus_ids)->
        //     get()->result_array();
            
        $this->db->select('classes.class_id,classes.name,classes.session');
        $this->db->from('classes');
        $this->db->join('campuses','campuses.campus_id=classes.campus_id','INNER');
        $this->db->join('courses','courses.course_id=classes.course_id','INNER');
        $this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $query = $this->db->get()->result_array();

        if(count($query)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
                'recovery_sessions'=>$query
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'recovery_sessions'=>'null'
            );
            echo json_encode($result);
        }

    }

    public function GetRecoveryFine(){

        $user_id = $this->input->post('user_id');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('campuses','campuses.campus_id=users.campus_id','INNER');
        $this->db->join('departments','departments.department_id=users.department_id','INNER');
        $this->db->where('users.user_id',$user_id);
        $user = $this->db->get()->result_array();
        $desigations = explode(",",$user[0]['designation_id']);
        foreach ($desigations as $desigation) {
            $recovery_management = @$this->db->get_where('recovery_management',array('designation_id'=>$desigation))->row()->recovery_management_id;
            if($recovery_management){
                $recovery_management_id = $recovery_management;
            }
        }


        $recovery = $this->db->get_where('recovery_management',array('recovery_management_id'=>$recovery_management_id))->result_array();
        $campus_ids = explode(',',$recovery[0]['campus_ids']);

        $data['recovery'] = $this->db->get_where('recovery_management',array('recovery_management_id'=>$recovery_management_id))->result_array();
        $campus_ids = explode(',',$data['recovery'][0]['campus_ids']);
        $campuses_ids = implode(',',$campus_ids);

        $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.paid_challans, payments.merged_challan,payments.challan_no, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->where_in('campuses.campus_id',$campuses_ids);
        $this->db->where('payments.merged_challan IS NOT NULL and payments.actual_amount > 0');
        $this->db->where(array('payments.actual_paid_date>='=>$start_date,'payments.actual_paid_date<='=>$end_date,'payments.paid'=>1,'students.status'=>1));
        $this->db->group_by("CASE WHEN payments.merged_challan IS NOT NULL THEN payments.merged_challan else '' end",false);
        $datafine_students = $this->db->get()->result_array();

        $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.merged_challan,payments.challan_no, payments.paid_challans, payments.dead_line, payments.fine_amount, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->where_in('campuses.campus_id',$campuses_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$start_date,'payments.actual_paid_date<='=>$end_date,'payments.paid'=>1,'students.status'=>1));
        $this->db->where('payments.merged_challan is null');
        $this->db->or_where('merged_challan IS not NULL and actual_amount = 0');
        $datapaid_payments_fine_students = $this->db->get()->result_array();

        $fine_students = array_merge($datafine_students,$datapaid_payments_fine_students);
        $fine_data[][]=json_encode($fine_students);
        $data['fine_student'] = $fine_data;
        $data['fine_student']=$data['fine_student'][0];
        $data['fine_student']= json_decode($data['fine_student'][0], true);

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Found',
            'fine_response'=>$data['fine_student']
        );
        echo json_encode($result);
    }

    public function GetAllEnteries(){

        $user_id = $this->input->post('user_id');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('campuses','campuses.campus_id=users.campus_id','INNER');
        $this->db->join('departments','departments.department_id=users.department_id','INNER');
        $this->db->where('users.user_id',$user_id);
        $user = $this->db->get()->result_array();
        $desigations = explode(",",$user[0]['designation_id']);
        foreach ($desigations as $desigation) {
            $recovery_management = @$this->db->get_where('recovery_management',array('designation_id'=>$desigation))->row()->recovery_management_id;
            if($recovery_management){
                $recovery_management_id = $recovery_management;
            }
        }
        $recovery = $this->db->get_where('recovery_management',array('recovery_management_id'=>$recovery_management_id))->result_array();
        $campus_ids = explode(',',$recovery[0]['campus_ids']);
        $course_ids = explode(',',$recovery[0]['course_id']);

        $data['recovery'] = $this->db->get_where('recovery_management',array('recovery_management_id'=>$recovery_management_id))->result_array();
        $campus_ids = explode(',',$data['recovery'][0]['campus_ids']);
        $campuses_ids = implode(',',$campus_ids);

        $this->db->select("payments.id as fee_id,'0' as isdel, 'UnPaid' as Fstatus,payments.split as split,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.dead_line<='=>$end_date,'payments.paid'=>0,'students.status'=>1));
        $unpaid_payments_students = $this->db->get()->result_array();

        //GET ALL UNPAID FEE PAYMENTS DETAILS OF CONTRACTS
        $this->db->select("*,payments.id as fee_id,'0' as isdel,'UnPaid' as Fstatus,payments.split as split");
        $this->db->from('payments');
        $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.dead_line<='=>$end_date,'payments.paid'=>0,'payments.amount !='=>'4500'));
        $unpaid_payments_contracts = $this->db->get()->result_array();

        //GET FEE PAYMENTS DETAILS OF STUDENTS
        $this->db->select("payments.id as fee_id,'0' as isdel, 'Paid' as Fstatus,payments.amount,payments.split as split, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
        $this->db->from('payments');
        $this->db->join('students','students.student_id=payments.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$start_date,'payments.actual_paid_date<='=>$end_date,'payments.paid'=>1,'students.status'=>1));
        $paid_payments_students = $this->db->get()->result_array();


        //GET FEE PAYMENTS DETAILS OF CONTRACTS
        $this->db->select("*,'0' as isdel,payments.id as fee_id,'Paid' as Fstatus,payments.split as split");
        $this->db->from('payments');
        $this->db->join('contracts','contracts.contract_id=payments.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('payments.actual_paid_date>='=>$start_date,'payments.actual_paid_date<='=>$end_date,'payments.paid'=>1,'payments.amount !='=>'4500'));
        $paid_payments_contracts = $this->db->get()->result_array();

        //GET SHIFTED PAYMENTS DETAILS OF STUDENTS
        $this->db->select("update_payment_requests.add_by,update_payment_requests.last_edit,0 as split,update_payment_requests.del as isdel,update_payment_requests.reason as delreason,update_payment_requests.id as fee_id,'shifted' as Fstatus,update_payment_requests.amount, update_payment_requests.dead_line, update_payment_requests.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee");
        $this->db->from('update_payment_requests');
        $this->db->join('payments','payments.challan_no=update_payment_requests.challan_no','left');
        $this->db->join('students','students.student_id=update_payment_requests.student_id','INNER');
        $this->db->join('classes','classes.class_id=students.class_id','INNER');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=students.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('update_payment_requests.update_date>='=>$start_date,'update_payment_requests.update_date<='=>$end_date,'update_payment_requests.ok_by_admin'=>1));
        $shifted_payments_students = $this->db->get()->result_array();

        //GET SHIFTED PAYMENTS DETAILS OF CONTRACTS
        $this->db->select("update_payment_requests.add_by,update_payment_requests.last_edit,0 as split,update_payment_requests.del as isdel,update_payment_requests.reason as delreason,payments.id as fee_id,'shifted' as Fstatus");
        $this->db->from('update_payment_requests');
        $this->db->join('payments','payments.challan_no=update_payment_requests.challan_no','left');
        $this->db->join('contracts','contracts.contract_id=update_payment_requests.contract_id','INNER');
        $this->db->join('contractors','contractors.contractor_id=contracts.contractor_id','INNER');
        $this->db->join('campuses','contracts.campus_id=campuses.campus_id','INNER');
        $this->db->join('courses','courses.course_id=contracts.course_id','INNER');
		$this->db->where_in('courses.course_id',$course_ids);
        $this->db->where_in('campuses.campus_id',$campus_ids);
        $this->db->where(array('update_payment_requests.update_date>='=>$start_date,'update_payment_requests.update_date<='=>$end_date,'update_payment_requests.ok_by_admin'=>1,'payments.amount !='=>'4500'));
        $shifted_payments_contracts = $this->db->get()->result_array();


        $data=array_merge($unpaid_payments_students,$paid_payments_students,$shifted_payments_students);

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Found',
            'all_students_enteries'=>$data
        );
        echo json_encode($result);

        // $data['contracts_fee_dues_comments']=array_merge($unpaid_payments_contracts,$paid_payments_contracts,$shifted_payments_contracts);

    }

    public function GetRecoveryStudents(){

        $user_id = $this->input->post('user_id');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $session = $this->input->post('session');
        $is_session = $this->input->post('is_session');

        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('campuses','campuses.campus_id=users.campus_id','INNER');
        $this->db->join('departments','departments.department_id=users.department_id','INNER');
        $this->db->where('users.user_id',$user_id);
        $user = $this->db->get()->result_array();

        $desigations = explode(",",$user[0]['designation_id']);
        foreach ($desigations as $desigation) {
            $recovery_management = @$this->db->get_where('recovery_management',array('designation_id'=>$desigation))->row()->recovery_management_id;
            if($recovery_management){
                $recovery_management_id = $recovery_management;
            }
        }
        $recovery = $this->db->get_where('recovery_management',array('recovery_management_id'=>$recovery_management_id))->result_array();
        $campus_ids = explode(',',$recovery[0]['campus_ids']);
        $course_ids = explode(',',$recovery[0]['course_id']);

        $data['recovery'] = $this->db->get_where('recovery_management',array('recovery_management_id'=>$recovery_management_id))->result_array();
        $campus_ids = explode(',',$data['recovery'][0]['campus_ids']);
        $campuses_ids = implode(',',$campus_ids);
        $courses_ids = implode(',',$course_ids);

        $current_date = date('Y-m-d');

        if ($is_session=='true'){
            $qry = "SELECT CASE WHEN (SELECT paid_on_date FROM fees_remarks WHERE fees_remarks.fee_remarks_id = MAX(tbl.fee_remarks_id)) <= '".$current_date."' AND (SELECT comment FROM fees_remarks WHERE fees_remarks.fee_remarks_id = MAX(tbl.fee_remarks_id)) LIKE 'Will pay on%' THEN 'WILL PAY TODAY'
            WHEN (SELECT comment FROM fees_remarks WHERE fees_remarks.fee_remarks_id = MAX(tbl.fee_remarks_id)) LIKE 'Will pay on%' THEN 'WILL PAY ON' 
            WHEN (SELECT comment FROM fees_remarks WHERE fees_remarks.fee_remarks_id = MAX(tbl.fee_remarks_id)) LIKE 'Cell off%' THEN 'CELL OFF' 
            WHEN (SELECT comment FROM fees_remarks WHERE fees_remarks.fee_remarks_id = MAX(tbl.fee_remarks_id)) LIKE 'Call not attended%' THEN 'CALL NOT ATTENDED' 
            WHEN (SELECT comment FROM fees_remarks WHERE fees_remarks.fee_remarks_id = MAX(tbl.fee_remarks_id)) LIKE 'Struck of now%' THEN 'STRUCK OFF NOW' 
            ELSE 'FRESH'
            END as TYPE, tbl.* , MAX(comment) as cmnt FROM (SELECT fees_remarks.fee_remarks_id, fees_remarks.paid_on_date,
            fees_remarks.comment, payments.id as fee_id,'0' as isdel, fee_rules.total_fee as total_course_fee, 'UnPaid' as Fstatus,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee FROM payments 
    	    INNER JOIN students ON students.student_id=payments.student_id 
    	    INNER JOIN classes ON classes.class_id=students.class_id 
    	    INNER JOIN campuses ON classes.campus_id=campuses.campus_id
    	    INNER JOIN courses ON courses.course_id=students.course_id
            LEFT JOIN fees_remarks ON fees_remarks.fee_id = payments.id
            LEFT JOIN fee_rules ON students.course_id = fee_rules.course_id 
            WHERE courses.course_id IN ($courses_ids) AND campuses.campus_id IN ($campuses_ids) AND payments.dead_line<='".$end_date."' AND payments.paid = 0 AND students.status = 1 AND classes.session = '".$session."' ORDER BY fees_remarks.date DESC) tbl GROUP by student_id";
        } else {
            $qry = "SELECT CASE WHEN (SELECT paid_on_date FROM fees_remarks WHERE fees_remarks.fee_remarks_id = MAX(tbl.fee_remarks_id)) <= '".$current_date."' AND (SELECT comment FROM fees_remarks WHERE fees_remarks.fee_remarks_id = MAX(tbl.fee_remarks_id)) LIKE 'Will pay on%' THEN 'WILL PAY TODAY'
            WHEN (SELECT comment FROM fees_remarks WHERE fees_remarks.fee_remarks_id = MAX(tbl.fee_remarks_id)) LIKE 'Will pay on%' THEN 'WILL PAY ON' 
            WHEN (SELECT comment FROM fees_remarks WHERE fees_remarks.fee_remarks_id = MAX(tbl.fee_remarks_id)) LIKE 'Cell off%' THEN 'CELL OFF' 
            WHEN (SELECT comment FROM fees_remarks WHERE fees_remarks.fee_remarks_id = MAX(tbl.fee_remarks_id)) LIKE 'Call not attended%' THEN 'CALL NOT ATTENDED' 
            WHEN (SELECT comment FROM fees_remarks WHERE fees_remarks.fee_remarks_id = MAX(tbl.fee_remarks_id)) LIKE 'Struck of now%' THEN 'STRUCK OFF NOW' 
            ELSE 'FRESH'
            END as TYPE, tbl.* , MAX(comment) as cmnt FROM (SELECT fees_remarks.fee_remarks_id, fees_remarks.paid_on_date,
             fees_remarks.comment, payments.id as fee_id,'0' as isdel, fee_rules.total_fee as total_course_fee, 'UnPaid' as Fstatus,payments.amount, payments.dead_line, payments.extra_amount, students.first_name, students.last_name, students.mobile, classes.name as class_name, students.roll_no, students.emergency_no, students.cnic, campuses.campus_name,classes.class_id,campuses.campus_id,students.student_id,students.total_fee FROM payments 
        	INNER JOIN students ON students.student_id=payments.student_id 
        	INNER JOIN classes ON classes.class_id=students.class_id 
        	INNER JOIN campuses ON classes.campus_id=campuses.campus_id
        	INNER JOIN courses ON courses.course_id=students.course_id
            LEFT JOIN fees_remarks ON fees_remarks.fee_id = payments.id
            LEFT JOIN fee_rules ON students.course_id = fee_rules.course_id 
            WHERE courses.course_id IN ($courses_ids) AND campuses.campus_id IN ($campuses_ids) AND payments.dead_line<='".$end_date."' AND payments.paid = 0 AND students.status = 1 ORDER BY fees_remarks.date DESC) tbl GROUP by student_id";
        }
        $data = $this->db->query($qry)->result_array();

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Found',
            'recovery_summary_students'=>$data
        );
        echo json_encode($result);

    }

    public function GetRecoveryUsers(){

        $qry = "SELECT recovery_management.recovery_management_id, designations.designation_name, departments.department_name, users.user_id, users.first_name, users.last_name, users.father_name, GROUP_CONCAT(DISTINCT campuses.campus_name) as campus_names, GROUP_CONCAT(DISTINCT CONCAT('Commission: ', recovery_management_rules.start, '% - ', recovery_management_rules.end, '% = ', recovery_management_rules.comission)) as rules FROM recovery_management 
INNER JOIN designations ON designations.designation_id = recovery_management.designation_id
LEFT JOIN departments ON departments.department_id = designations.designation_id
INNER JOIN users ON users.designation_id = designations.designation_id OR FIND_IN_SET(users.designation_id, designations.designation_id)
INNER JOIN recovery_management_rules ON recovery_management_rules.recovery_management_id = recovery_management.recovery_management_id
INNER JOIN campuses ON FIND_IN_SET(campuses.campus_id, recovery_management.campus_ids)
GROUP BY recovery_management.designation_id";
        $data = $this->db->query($qry)->result_array();

        if(count($data)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'recovery_users_response'=>$data
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'recovery_users_response'=>'null'
            );
            echo json_encode($result);


        }

    }

    public function GetDynamicSyllabus(){

        $total_days = 20;

        $query = "SELECT SUM(credibility) as total_credibility, $total_days as total_days, $total_days/SUM(credibility) as per_day_cover FROM `topics` WHERE course_id = 2";
        $q_result = $this->db->query($query)->result_array();

        $per_day_cover = $q_result[0]['per_day_cover'];

        $qry = "SELECT (credibility * $per_day_cover) as single_cover, topics.* FROM `topics` WHERE course_id = 2";
        $data = $this->db->query($qry)->result_array();

        $sub_array = array();
        $array= array();
        for ($x = 0; $x <= $total_days; $x++) {
            foreach($data as $syllabus){
                $item = $syllabus['single_cover'] - $x;
                if($item>1){
                    $array = array("Topic"=>$syllabus['topic_name']);
                    break;
                } else if ($item<1){

                }
            }
            if(is_array($sub_array)){
                $sub_array = array_push($sub_array, array("Day ".$x =>array($array)));
            } else {
                $sub_array = array("Day ".$x =>array($array));
            }
            //echo "The number is: $x <br>";
        }
        $json_array = array($sub_array);
        echo json_encode($json_array);
    }

    public function GetSyllabus(){

        // $session = $this->input->post('session');
        // $subject = $this->input->post('subject');
        $lecture_id = $this->input->post('lecture_id');

        $this->db->query("SET @@group_concat_max_len = 10000;");
        $qry = "SELECT session_syllabus.*, GROUP_CONCAT(CONCAT(t1.topic_name, ' = ', t1.chapter_id) SEPARATOR '---') as topic_names, GROUP_CONCAT(DISTINCT t1.chapter_id) as chapters, GROUP_CONCAT(DISTINCT CONCAT(c1.chapter_name, ' = ', c1.chapter_id) SEPARATOR '---') as chapters_names, temp.practical_topic_names FROM `session_syllabus` 
INNER JOIN topics as t1 ON FIND_IN_SET(t1.topic_id, topic_ids) 
INNER JOIN chapters as c1 ON FIND_IN_SET(c1.chapter_id, t1.chapter_id) 
INNER JOIN (SELECT id, GROUP_CONCAT(CONCAT(t1.topic_name, ' = ', t1.chapter_id)) as practical_topic_names FROM `session_syllabus` 
INNER JOIN topics as t1 ON FIND_IN_SET(t1.topic_id, practical_ids) 
GROUP BY id) as temp ON temp.id = session_syllabus.id WHERE session_syllabus.lecture_id = '".$lecture_id."'
GROUP BY id ORDER BY session_syllabus.date ASC";
        $data = $this->db->query($qry)->result_array();

        if(count($data)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'syllabus_response'=>$data
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'syllabus_response'=>'null'
            );
            echo json_encode($result);


        }

    }

    public function GetRecoveryDetails(){

        $student_id = $this->input->post('student_id');
        $fee_id = $this->input->post('fee_id');

        $this->db->order_by('paid_date','DESC');
        $payments=$this->db->get_where('payments',array('student_id'=>$student_id))->result_array();
        $total_fee = 0;
        $created_council_fee = 0;
        $submitted_council_fee = 0;
        $fee_decided_current_time = 0;
        $total_fee_submitted = 0;
        $unpaid_extra_fee = 0;
        $unpaid_fine_amount = 0;
        $unpaid_council_fee = 0;
        $remaining_unpaid_fee = 0;
        $unpaid_installments_current_time = 0;
        foreach($payments as $payment)
        {
            if($payment['payment_plan']!='consulation fee' && $payment['payment_comment']=='College Fee')
            {
                $total_fee+=$payment['amount'];
            }
            if($payment['payment_plan']=='consulation fee')
            {
                $created_council_fee+=$payment['amount'];
                if($payment['paid']==1)
                {
                    $submitted_council_fee+=$payment['actual_amount'];
                }
            }
            if($payment['dead_line']<date('Y-m-d'))
            {
                $fee_decided_current_time+=$payment['amount'];
                if($payment['paid']==0)
                {
                    $unpaid_installments_current_time++;
                }
            }
            if($payment['paid']==1 && $payment['payment_plan']!='consulation fee')
            {
                $total_fee_submitted+=$payment['amount'];
            }
            if($payment['paid']==0 && $payment['payment_plan']!='consulation fee')
            {
                $unpaid_extra_fee+=$payment['extra_amount'];
            }
            if($payment['paid']==0 && $payment['payment_plan']!='consulation fee')
            {
                $unpaid_fine_amount+=$payment['fine_amount']+$payment['shifted_fine']+$payment['shifted_previous_fine']+$payment['removed_fine'];
            }
            if($payment['paid']==0 && $payment['payment_plan']=='consulation fee')
            {
                $unpaid_council_fee+=$payment['amount'];
            }
            if($payment['paid']==0 && $payment['payment_plan']!='consulation fee' && $payment['dead_line']<date('Y-m-d'))
            {
                $remaining_unpaid_fee+=$payment['amount'];
            }
            //CHECK ANY PAYMENT 1 MONTH BEFORE
            $oneMonthOldDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
            if($payment['paid']==0 && $payment['dead_line']<$oneMonthOldDate)
            {
                $show=1;
            }
        }

        $payments_paid = array();
        $payments_unpaid = array();

        foreach($payments as $payment)
        {
            if($payment['paid']==1)
            {
                $payments_paid[] = array('payment_paid'=>$payment['actual_amount'], 'paid_on'=>$payment['paid_date']);
            }
        }

        foreach($payments as $payment)
        {
            if($payment['paid']==0)
            {
                if($payment['dead_line']<date('Y-m-d'))
                {
                    $payments_unpaid[] = array('payment_unpaid'=>$payment['amount'], 'deadline'=>$payment['dead_line'], 'type'=>'critical');
                }
                else
                {
                    $payments_unpaid[] = array('payment_unpaid'=>$payment['amount'], 'deadline'=>$payment['dead_line'], 'type'=>'normal');
                }
            }
        }
        // $payments_unpaid = array_reverse($payments_unpaid);

        $remarks_arr = array();

        $this->db->order_by('date','DESC');
        $remarks = $this->db->get_where('fees_remarks', array('fee_id'=>$fee_id))->result_array();
        foreach($remarks as $remark){
            $remarks_arr[] = array('comments'=> @$remark['comment'], 'add_by'=>@$remark['add_by'] , 'date'=>@date('d M, Y H:i:s A',strtotime(@$remark['date'])));



        }

        $data1 = array();

        $data = array('total_created_fee' => $total_fee,
            'create_council_fee'=>$created_council_fee,
            'submitted_council_fee'=>$submitted_council_fee,
            'fee_decided_current_time'=>$fee_decided_current_time,
            'submitted_fee'=>$total_fee_submitted,
            'remaining_fee_payable'=>$fee_decided_current_time-$total_fee_submitted,
            'unpaid_installments'=>$unpaid_installments_current_time,
            'percentage'=>round(($total_fee_submitted / $total_fee) * 100),
            'percentage_paid_fee'=>round(($total_fee_submitted/$fee_decided_current_time)*100),
            'unpaid_council_fee'=>$unpaid_council_fee,
            'unpaid_fine_amount'=>$unpaid_fine_amount,
            'unpaid_extra_fee'=>$unpaid_extra_fee,
            'remaining_unpaid_fee'=>$remaining_unpaid_fee,
            'paid_payments'=>$payments_paid,
            'unpaid_payments'=>$payments_unpaid,
            'remarks'=>$remarks_arr
        );

        array_push($data1, $data);

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Found',
            'recovery_summary_details'=>$data1
        );
        echo json_encode($result);

    }

    public function SubmitComment(){

        $student_id = $this->input->post('student_id');
        $username = $this->input->post('username');
        $fee_id = $this->input->post('fee_id');
        $description = $this->input->post('description');
        $comment = $this->input->post('comment');
        $selected_date = $this->input->post('selected_date');

        $check_fee = $this->db->get_where('fees_remarks', array('fee_id'=>$fee_id))->result_array();
        $original_fee_entry = $this->db->get_where('payments', array('id'=>$fee_id))->result_array();

        $payments=$this->db->get_where('payments',array('student_id'=>$student_id))->result_array();


        $entries="";
        foreach ($payments as $astx){


            if( $astx['paid']==0  && $astx['dead_line']<date('Y-m-d'))
            {
                $entries .=$astx['challan_no'].",";
            }


        }

        $this->db->set('fee_id', $fee_id);
        $this->db->set('comment', $comment." " .$selected_date. " ".$description."  for Challan no (".$entries .") ");
        $this->db->set('paid_on_date', $selected_date);
        $this->db->set('add_by', $username);
        $this->db->set('clear_status', '1');
        $results = $this->db->insert('fees_remarks');

        if ($results){
            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Inserted Successfully',
            );
            echo json_encode($result);
        } else {
            $result = array(
                'status'=>'FAILED',
                'response_code'=>'0',
                'message'=>'An error',
            );
            echo json_encode($result);
        }
    }

    public function RecoveryLog(){

        $fee_id = $this->input->post('fee_id');
        $student_id = $this->input->post('student_id');
        $user_id = $this->input->post('user_id');
        $from_no = $this->input->post('from_no');
        $to_no = $this->input->post('to_no');
        $call_duration = $this->input->post('call_duration');
        $call_placing_time = $this->input->post('call_placing_time');
        $call_end_time = $this->input->post('call_end_time');
        $call_status = $this->input->post('call_status');

        $this->db->set(array(
                'fee_id'=>$fee_id,
                'student_id'=>$student_id,
                'user_id'=>$user_id,
                'from_no'=>$from_no,
                'to_no'=>$to_no,
                'call_duration'=>$call_duration,
                'call_placing_time'=>$call_placing_time,
                'call_end_time'=>$call_end_time,
                'call_status'=>$call_status,
                'date'=>date('Y-m-d')
            )
        );

        $results=$this->db->insert('recovery_logs');
        $insert_id = $this->db->insert_id();

        if ($results){
            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Inserted Successfully',
                'recovery_id'=>$insert_id
            );
            echo json_encode($result);
        } else {
            $result = array(
                'status'=>'FAILED',
                'response_code'=>'0',
                'message'=>'An error',
                'recovery_id'=>null
            );
            echo json_encode($result);
        }
    }

    public function RecoveryAllLog(){

        $fee_id = $this->input->post('fee_id');
        $student_id = $this->input->post('student_id');
        $user_id = $this->input->post('user_id');
        $time = $this->input->post('time');
        $type = $this->input->post('type');

        $this->db->set(array(
                'fee_id'=>$fee_id,
                'student_id'=>$student_id,
                'user_id'=>$user_id,
                'time'=>$time,
                'type'=>$type,
                'date'=>date('Y-m-d')
            )
        );

        $results=$this->db->insert('recovery_all_logs');
        $insert_id = $this->db->insert_id();

        if ($results){
            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Inserted Successfully',
                'recovery_all_log_id'=>$insert_id
            );
            echo json_encode($result);
        } else {
            $result = array(
                'status'=>'FAILED',
                'response_code'=>'0',
                'message'=>'An error',
                'recovery_all_log_id'=>null
            );
            echo json_encode($result);
        }
    }

    public function GetRecoveryCallLogs(){

        $user_id = $this->input->post('user_id');

        $qry = "SELECT recovery_logs.*, students.first_name, students.last_name, students.city, students.roll_no FROM `recovery_logs` INNER JOIN students ON students.student_id = recovery_logs.student_id
WHERE user_id = '".$user_id."' GROUP BY to_no, call_duration, call_placing_time";
        $query = $this->db->query($qry)->result_array();

        if(count($query)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
                'recovery_call_logs_response'=>$query,
                'records'=>count($query)
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'recovery_call_logs_response'=>'null',
                'records'=>count($query)
            );
            echo json_encode($result);
        }

    }

    public function GetRecoveryAllLogs(){

        $user_id = $this->input->post('user_id');

        $qry = "SELECT recovery_all_logs.*, students.first_name, students.last_name, students.city, students.roll_no FROM `recovery_all_logs` INNER JOIN students ON students.student_id = recovery_all_logs.student_id
WHERE user_id = '".$user_id."' GROUP BY time";
        $query = $this->db->query($qry)->result_array();

        if(count($query)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully',
                'recovery_all_logs_response'=>$query,
                'records'=>count($query)
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'recovery_all_logs_response'=>'null',
                'records'=>count($query)
            );
            echo json_encode($result);
        }

    }

    public function GetComplaints(){

        $staff_id = $this->input->post('staff_id');
        $designation_id = $this->input->post('designation_id');

        if($designation_id==24){
            $this->db->select(' * ');
            $this->db->from('complaints');
            $complaints = $this->db->get()->result_array();
        } else {
            $this->db->select(' * ');
            $this->db->from('complaints');
            $this->db->where(array('assign_id'=>$staff_id));
            $complaints = $this->db->get()->result_array();
        }

        if(count($complaints)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'complaints_response'=>$complaints
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'complaints_response'=>$complaints
            );
            echo json_encode($result);


        }

    }

    public function GetChats(){

        $staff_id = $this->input->post('staff_id');
        $designation_id = $this->input->post('designation_id');

//        if($designation_id==24 || $designation_id==10){
        $qry = "SELECT chats.*, issue_types.name as issue FROM `chats` LEFT JOIN issue_types ON issue_types.id = chats.category  order by chat_id DESC";
        $chats = $this->db->query($qry)->result_array();
//        } else {
//            $qry = "SELECT chats.*, issue_types.name as issue FROM `chats` LEFT JOIN issue_types ON issue_types.id = chats.category WHERE assign_id = $staff_id";
//            $chats = $this->db->query($qry)->result_array();
//        }

        if(count($chats)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'chats_response'=>$chats
            );
            echo json_encode($result);

        }
        else {

            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'chats_response'=>$chats
            );
            echo json_encode($result);

        }

    }

    public function GetStaff(){

        // $staff_id = $this->input->post('staff_id');
        // $designation_id = $this->input->post('designation_id');

        // if($designation_id==24){
        $this->db->select(' * ');
        $this->db->from('users');
        $this->db->where(array('status'=>1));
        $staff = $this->db->get()->result_array();
        //     } else {
        // 		$this->db->select(' * ');
        // 		$this->db->from('chats');
        // 		$this->db->where(array('assign_id'=>$staff_id));
        // 		$chats = $this->db->get()->result_array();
        //     }

        if(count($staff)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'staff_response'=>$staff
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'staff_response'=>$staff
            );
            echo json_encode($result);


        }

    }

    public function GetCampusesApplications(){
        // $teacher = $this->input->post('teacher');

        $qry = "SELECT COUNT(*) as num_of_applications, admission_applications.campus_id, admission_applications.course_id, campuses.campus_name FROM `admission_applications` INNER JOIN campuses ON campuses.campus_id = admission_applications.campus_id GROUP BY campus_id";
        $query = $this->db->query($qry)->result_array();

        if(count($query)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
                'admissions_campuses_response'=>$query
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'admissions_campuses_response'=>'null'
            );
            echo json_encode($result);
        }
    }

    public function GetAdmissions(){

// 		$this->db->select('*');
// 		$this->db->from('admission_applications');
// // 		$this->db->where(array('teacher'=>$teacher));
        $qry = "SELECT admission_applications.*, courses.course_name, campuses.campus_name FROM `admission_applications` INNER JOIN courses ON courses.course_id = admission_applications.course_id INNER JOIN campuses ON campuses.campus_id = admission_applications.campus_id";
        $query = $this->db->query($qry)->result_array();

        if(count($query)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
                'admissions_response'=>$query
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'admissions_response'=>'null'
            );
            echo json_encode($result);
        }
    }

    public function GetNewOnlineApplications(){

        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        $user_id = $this->input->post('user_id');
        $user = $this->db->get_where("users","user_id = '$user_id'")->row();
        $today = date("Y-m-d");

        $query = $this->db->select("*,
        CASE 
        WHEN ((select count(*) from students where mobile = apply_now.mobile or emergency_no = apply_now.mobile) != 0) THEN 'Already Student'
        WHEN ((select count(*) from apply_now b where b.apply_now_id != apply_now.apply_now_id and (b.mobile = apply_now.mobile or b.emergency_no = apply_now.mobile)) != 0) THEN 'Already Applied'
        WHEN apply_now.status = 0 THEN 'First Time'
        END AS status_type
        ")
            ->order_by("apply_now.date","DESC")
            ->get_where('apply_now',array('status'=>0,'clear_by_admin'=>0,'pending_status'=>NULL,'date >='=>$start_date." 00:00:00",'date <='=>$end_date." 23:59:59"))
            ->result_array();

        $this->db->select("apply_now.*,IF(MAX(online_application_comments.next_date_for_call) <= '$today', 'Today', 'Future') as status_type")
            ->join("online_application_comments","online_application_comments.apply_now_id = apply_now.apply_now_id")
            ->order_by('online_application_comments.online_application_comment_id','DESC')
            ->group_by("apply_now.apply_now_id");
        if ($user->role == 'admin') {
            $query_1 = $this->db->where(array('apply_now.pending_status' => 1, 'apply_now.status' => 0, 'apply_now.date >=' => $start_date." 00:00:00", 'apply_now.date <=' => $end_date." 23:59:59"))
                ->order_by("apply_now.date", "DESC")
                ->get('apply_now')->result_array();
        }else{
            $query_1 = $this->db->where(array('apply_now.pending_status' => 1, 'apply_now.status' => 0,
                'online_application_comments.add_by' => $user->first_name . ' ' . $user->last_name, 'apply_now.date >=' => $start_date." 00:00:00", 'apply_now.date <=' => $end_date." 23:59:59"))
                ->order_by("apply_now.date", "DESC")
                ->get('apply_now')->result_array();
        }

        $all_data = array_merge($query,$query_1);

        if(count($all_data)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
                'online_applications_response'=>$all_data
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'online_applications_response'=>'null'
            );
            echo json_encode($result);
        }
    }

    public function GetOnlineApplications(){

        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        $qry = "SELECT * FROM (SELECT *, CASE WHEN t.roll_no IS NULL THEN interest_type ELSE 'Already Student' END AS type FROM (
                SELECT COUNT(*) no_of_apply, apply_now.apply_now_id as id, name as student_name, website as source, apply_now.father_name, apply_now.cnic, apply_now.gender, apply_now.date_of_birth, education as edu, apply_now.address, apply_now.city, apply_now.mobile, apply_now.emergency_no, IFNULL(online_application_comments.interest_type, 'Unchecked') as interest_type, IFNULL(online_application_comments.next_date_for_call, 'NO') as next_date_for_call, IFNULL(students.roll_no, '0') as student_roll_no, online_application_comments.comment, online_application_comments.add_date_time, 'online_application' as application_type, online_application_comments.add_by, students.roll_no FROM apply_now LEFT JOIN online_application_comments ON online_application_comments.apply_now_id = apply_now.apply_now_id AND online_application_comments.application_type = 'online_application' LEFT JOIN students ON students.cnic = apply_now.cnic GROUP by apply_now.cnic
                UNION
                SELECT COUNT(*) no_of_apply, application_id as id, CONCAT(admission_applications.first_name, \" \", admission_applications.last_name) as student_name, 'mobile' as source, admission_applications.father_name, admission_applications.cnic, admission_applications.gender, admission_applications.date_of_birth, admission_applications.qualification as edu, admission_applications.address, admission_applications.city, admission_applications.mobile, admission_applications.emergency_no, IFNULL(online_application_comments.interest_type, 'Unchecked') as interest_type, IFNULL(online_application_comments.next_date_for_call, 'NO') as next_date_for_call, IFNULL(students.roll_no, '0') as student_roll_no, online_application_comments.comment, online_application_comments.add_date_time, 'admission_application' as application_type, online_application_comments.add_by, students.roll_no FROM admission_applications LEFT JOIN online_application_comments ON online_application_comments.apply_now_id = admission_applications.application_id AND online_application_comments.application_type = 'admission_application' LEFT JOIN students ON students.cnic = admission_applications.cnic GROUP BY admission_applications.cnic
                ORDER BY `add_date_time`  DESC) t GROUP BY id  
                ORDER BY `t`.`id`  DESC) p WHERE type NOT IN ('Already Student', 'Not Interested') AND add_date_time BETWEEN '".$start_date.'%'."' AND '".$end_date.'%'."'";
        $query = $this->db->query($qry)->result_array();

        if(count($query)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
                'online_applications_response'=>$query
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'online_applications_response'=>'null'
            );
            echo json_encode($result);
        }

    }

    public function GetOnlineApplicationsOld(){

        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        $qry = "SELECT * FROM (SELECT *, CASE WHEN t.roll_no IS NULL THEN interest_type ELSE 'Already Student' END AS type FROM (
                SELECT COUNT(*) no_of_apply, apply_now.apply_now_id as id, name as student_name, website as source, apply_now.father_name, apply_now.cnic, apply_now.gender, apply_now.date_of_birth, education as edu, apply_now.address, apply_now.city, apply_now.mobile, apply_now.emergency_no, IFNULL(online_application_comments.interest_type, 'Unchecked') as interest_type, IFNULL(online_application_comments.next_date_for_call, 'NO') as next_date_for_call, IFNULL(students.roll_no, '0') as student_roll_no, online_application_comments.comment, online_application_comments.add_date_time, 'online_application' as application_type, online_application_comments.add_by, students.roll_no FROM apply_now LEFT JOIN online_application_comments ON online_application_comments.apply_now_id = apply_now.apply_now_id AND online_application_comments.application_type = 'online_application' LEFT JOIN students ON students.cnic = apply_now.cnic GROUP by apply_now.cnic
                UNION
                SELECT COUNT(*) no_of_apply, application_id as id, CONCAT(admission_applications.first_name, \" \", admission_applications.last_name) as student_name, 'mobile' as source, admission_applications.father_name, admission_applications.cnic, admission_applications.gender, admission_applications.date_of_birth, admission_applications.qualification as edu, admission_applications.address, admission_applications.city, admission_applications.mobile, admission_applications.emergency_no, IFNULL(online_application_comments.interest_type, 'Unchecked') as interest_type, IFNULL(online_application_comments.next_date_for_call, 'NO') as next_date_for_call, IFNULL(students.roll_no, '0') as student_roll_no, online_application_comments.comment, online_application_comments.add_date_time, 'admission_application' as application_type, online_application_comments.add_by, students.roll_no FROM admission_applications LEFT JOIN online_application_comments ON online_application_comments.apply_now_id = admission_applications.application_id AND online_application_comments.application_type = 'admission_application' LEFT JOIN students ON students.cnic = admission_applications.cnic GROUP BY admission_applications.cnic
                ORDER BY `add_date_time`  DESC) t GROUP BY id  
                ORDER BY `t`.`id`  DESC) p WHERE type NOT IN ('Unchecked', 'Stay For Future', 'Interested') AND add_date_time BETWEEN '".$start_date.'%'."' AND '".$end_date.'%'."'";
        $query = $this->db->query($qry)->result_array();

        if(count($query)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
                'old_online_applications_response'=>$query
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'old_online_applications_response'=>'null'
            );
            echo json_encode($result);
        }

    }

    public function GetOnlineApplication(){

        $application_id = $this->input->post('application_id');
        $type = $this->input->post('type');

//        if ($type=='online_application'){

        $qry = "SELECT apply_now.apply_now_id, name, website, father_name, cnic, gender, date_of_birth, education, address, city, mobile, emergency_no, IFNULL(online_application_comments.interest_type, 'Unchecked') as interest_type, IFNULL(online_application_comments.next_date_for_call, 'NO') as next_date_for_call, online_application_comments.comment, online_application_comments.add_date_time, online_application_comments.application_type, online_application_comments.add_by FROM apply_now LEFT JOIN online_application_comments ON online_application_comments.apply_now_id = apply_now.apply_now_id  WHERE apply_now.apply_now_id = $application_id";

//        } else {
//
//            $qry = "SELECT application_id as id, CONCAT(admission_applications.first_name, ' ', admission_applications.last_name) as student_name, 'mobile' as source, father_name, cnic, gender, date_of_birth, qualification as edu, address, city, mobile, emergency_no, IFNULL(online_application_comments.interest_type, 'Unchecked') as interest_type, IFNULL(online_application_comments.next_date_for_call, 'NO') as next_date_for_call, online_application_comments.comment, online_application_comments.add_date_time, online_application_comments.application_type, online_application_comments.add_by FROM admission_applications LEFT JOIN online_application_comments ON online_application_comments.apply_now_id = admission_applications.application_id AND online_application_comments.application_type = 'admission_application' WHERE admission_applications.application_id = $application_id";
//
//        }

        $query = $this->db->query($qry)->result_array();

        if(count($query)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
                'online_application_response'=>$query
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'online_application_response'=>'null'
            );
            echo json_encode($result);
        }

    }

    public function OnlineApplicationComment()
    {
        $interest_type = $this->input->post('interest_type');
        $date = $this->input->post('date');
        $next_date_for_call = $this->input->post('next_date_for_call');
        $comment = $this->input->post('comment');
        $add_by = $this->input->post('add_by');
        $apply_now_id = $this->input->post('apply_now_id');

        $this->db->set('interest_type',$interest_type);
        if($date==1)
        {
            $this->db->set('next_date_for_call',$next_date_for_call);
        }
        else
        {
            $this->db->set('next_date_for_call','0000-00-00');
        }
        $this->db->set('comment',$comment);
        $this->db->set('add_by',$add_by);
        $this->db->set('apply_now_id',$apply_now_id);
        $this->db->set('add_date_time',date('Y-m-d H:i:s'));
        $this->db->insert('online_application_comments');


        if($interest_type=='Not Interested')
        {
            $this->db->set('pending_status','0');
            $this->db->set('status','1');
        }
        else
        {
            $this->db->set('pending_status','1');
            $this->db->set('status','0');
        }
        $this->db->set('last_edit', $add_by);
        $this->db->where('apply_now_id', $apply_now_id);
        $update = $this->db->update('apply_now');

        if($update){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
            );
            echo json_encode($result);
        }
    }

    public function GetSingleAdmission(){

        $application_id = $this->input->post('application_id');

        $qry = "SELECT admission_applications.*, cnic_front as is_cnic, CONCAT('".'https://'.$_SERVER['HTTP_HOST'].'/ShahbazCollege/Admin/'."', student_image) as student_image, CONCAT('".'https://'.$_SERVER['HTTP_HOST'].'/ShahbazCollege/Admin/'."', matriculation_result) as matriculation_result, CONCAT('".'https://'.$_SERVER['HTTP_HOST'].'/ShahbazCollege/Admin/'."', cnic_front) as cnic_front, CONCAT('".'https://'.$_SERVER['HTTP_HOST'].'/ShahbazCollege/Admin/'."', cnic_back) as cnic_back, CONCAT('".'https://'.$_SERVER['HTTP_HOST'].'/ShahbazCollege/Admin/'."', b_form) as b_form, CONCAT('".'https://'.$_SERVER['HTTP_HOST'].'/ShahbazCollege/Admin/'."', signature) as signature , courses.course_name, campuses.campus_name FROM `admission_applications` 
        LEFT JOIN courses ON courses.course_id = admission_applications.course_id 
        LEFT JOIN campuses ON campuses.campus_id = admission_applications.campus_id 
        LEFT JOIN student_docs ON student_docs.application_id = admission_applications.application_id 
        WHERE admission_applications.application_id = '".$application_id."'";
        $query = $this->db->query($qry)->result_array();

        if(count($query)>0){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
                'single_admissions_response'=>$query
            );
            echo json_encode($result);
        } else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'single_admissions_response'=>'null'
            );
            echo json_encode($result);
        }
    }

    public function guestlogin(){


        $name = $this->input->post('name');
        $phone = $this->input->post('phone');
        $address = $this->input->post('address');
        $qual = $this->input->post('qual');
        $cnic = $this->input->post('cnic');



        $this->db->from('guest');
        $this->db->where(array('guest.cnic'=>$cnic));
        $alreadyfound = $this->db->get()->result_array();
        if(count($alreadyfound)>0)
        {


            $this->db->select('slider_images.*');
            $this->db->from('slider_images');
            $slider_images = $this->db->get()->result_array();


            $this->db->select('courses.*');
            $this->db->from('courses');
            $courses = $this->db->get()->result_array();



            $result = array(
                'status'=>'FOUND',
                'response_code'=>'2',
                'message'=>'ALREADY FOUND',
                'sliderimages'=>$slider_images,
                'courses'=>$courses,
                'response'=>$alreadyfound
            );
            echo json_encode($result);

        }else
        {

            $this->db->set(array(
                'name'=>$name,
                'phone'=>$phone,
                'address'=>$address,
                'qualification'=>$qual,
                'cnic'=>$cnic));
            $results=$this->db->insert('guest');
            $insert_id = $this->db->insert_id();


            $this->db->select('slider_images.*');
            $this->db->from('slider_images');
            $slider_images = $this->db->get()->result_array();


            $this->db->select('courses.*');
            $this->db->from('courses');
            $courses = $this->db->get()->result_array();


            if($results){

                $result = array(
                    'status'=>'SUCCESS',
                    'response_code'=>'1',
                    'message'=>'LOGGED IN AS GUEST',
                    'sliderimages'=>$slider_images,
                    'courses'=>$courses,
                    'response'=>$insert_id
                );
                echo json_encode($result);

            }else{


                $result = array(
                    'status'=>'SUCCESS',
                    'response_code'=>'2',
                    'message'=>'LOGGED IN AS GUEST',
                    'sliderimages'=>$slider_images,
                    'courses'=>$courses,
                    'response'=>$insert_id
                );
                echo json_encode($result);


            }

        }



    }

    public function check_staff()
    {
        //echo 'success';
        $roll_no = $this->input->post('cnic');


        $this->db->select("users.*,campuses.campus_name as campus_name, (select image from teacher_documents where teacher_id=users.user_id 
		and type = 'Photo') as profile_image");
        $this->db->from('users');
        $this->db->join('campuses','users.campus_id=campuses.campus_id','INNER');
        $this->db->where('users.cnic = ',$roll_no);
        $active_student = $this->db->get()->result_array();



        if(count($active_student)>0)
        {


            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'allow login',
                'response'=>$active_student
            );
            echo json_encode($result);
        }

        else
        {
            $result=array(
                'status'=>'ERROR',
                'response_code'=>'3',
                'message'=>'No User Found'
            );
            echo json_encode($result);
        }
    }

    public function GetAttendance()
    {
        //echo 'success';
        $user_id = $this->input->post('user_id');
        $strDateFrom = date('Y-m-1');
        $strDateTo = date('Y-m-d');
        $dates = $this->createDateRangeArray($strDateFrom,$strDateTo);

        $this->db->select('machine_data.*');
        $this->db->from('machine_data');
        $this->db->join('users','users.user_id=machine_data.teacher_student_id and machine_data.type = "teacher"','inner');
        $this->db->where(array('users.status'=>1,'users.user_id'=>$user_id));
        $user = $this->db->get()->row();
        $my_attendances = array();

        if($user != NULL)
        {
            $machine_user_id = $user->machine_id;
            foreach($dates as $key=>$date)
            {
                $array_date = array("date"=>$date,"in_time"=>"","out_time"=>"","status"=>"0");
                $qry = 'SELECT * FROM attendence WHERE machine_user_id='.$machine_user_id.' AND (time>="'.$date.' 00:00:00" AND time<"'.$date.' 23:59:59") ORDER BY time ASC LIMIT 1';
                $checkin_time = $this->db->query($qry)->result_array();
                if(count($checkin_time)>0)
                {
                    $array_date['in_time']= @date('h:i:s A', strtotime($checkin_time[0]['time']));
                    $array_date['status'] = "1";
                }
                else
                {
                    $array_date['in_time'] = '';
                    $array_date['status'] = "0";
                }

                $qry = 'SELECT * FROM attendence WHERE machine_user_id='.$machine_user_id.' AND (time>="'.$date.' 00:00:00.00" AND time<"'.$date.' 23:59:59.999") ORDER BY time DESC LIMIT 1';
                $checkout_time = $this->db->query($qry)->result_array();
                if(count($checkout_time)>0)
                {
                    $my_checkout = @date('h:i:s A', strtotime($checkout_time[0]['time']));

                    if($my_checkout != $array_date['in_time'])
                        $array_date['out_time']= @date('h:i:s A', strtotime($checkout_time[0]['time']));
                    else
                        $array_date['out_time']= '';
                }
                else
                {
                    $array_date['out_time']= '';
                }
                $Day = date('l', strtotime($date));
                $status = $this->get_staff_day_timing($user_id, $Day);

                if (!empty($status) && $array_date['in_time'] != "") {
                    if (strtotime($array_date['in_time']) < strtotime($status['half_day_on']))
                        $array_date['status'] = "1";
                    elseif (strtotime($array_date['in_time']) < strtotime($status['full_day_on']))
                        $array_date['status'] = "2";
                    else
                        $array_date['status'] = "3";
                }
                if ($array_date['status'] == "0" || $array_date['status'] == "3") {
                    $event = $this->db->where("fromdate >=",$date)
                        ->where("todate <=",$date)
                        ->where("empid",$user_id)
                        ->where("status","1")
                        ->get("tblleaves")->result_array();
                    if (count($event)>0) {
                        $array_date['status'] = "4";
                    }
                    if (!empty($status) && $this->is_off_day_timing($status)) {
                        $array_date['status'] = "5";
                    }

                }
                array_push($my_attendances,$array_date);
            }
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Data Found Successfully!',
                'attendances'=>$my_attendances
            );
            echo json_encode($result);
        }

        else
        {
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'attendances'=>'null'
            );
            echo json_encode($result);
        }
    }

    public function createDateRangeArray($strDateFrom,$strDateTo)
    {
        $aryRange=array();

        $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
        $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

        if ($iDateTo>=$iDateFrom)
        {
            array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
            while ($iDateFrom<$iDateTo)
            {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange,date('Y-m-d',$iDateFrom));
            }
        }
        return $aryRange;
    }

    public function GetLeaves()
    {
        //echo 'success';
        $user_id = $this->input->post('user_id');

        $this->db->select('tblleavetype.leavetype as leavetypename,tblleaves.*');
        $this->db->from('tblleaves');
        $this->db->join('tblleavetype','tblleavetype.id = tblleaves.leavetype','left');
        $leaves = $this->db->where("(tblleaves.empid='$user_id')")->get()->result_array();
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Data Found Successfully!',
            'leaves'=>$leaves
        );
        echo json_encode($result);

    }

    public function GetAdmissionsEntries()
    {
        //echo 'success';
        $user_id = $this->input->post('user_id');

        $access = $this->db->get_where('access', array('user_id'=>$user_id))->result_array();
        $campus_ids = @explode(',',$access[0]['campus_ids']);
        $role = $this->db->get_where("users","user_id = '$user_id'")->row()->role;

        $this->db->select('students.student_id, 
                students.course_id, 
                students.device_id, 
                students.study_campus, 
                students.first_name, 
                students.last_name, 
                students.father_name,
                students.roll_no, 
                students.plan_id, 
                students.gender,
                students.qualification, 
                students.caste, 
                students.religion, 
                students.email, 
                students.cnic, 
                students.blood_group, 
                students.date_of_birth, 
                students.registration_date, 
                students.total_fee, 
                students.city, 
                students.address, 
                students.mobile, 
                students.emergency_no, 
                students.class_id, 
                students.status,
                students.contractor_id, 
                students.contract_id,
                students.books_1, 
                students.books_2,
                students.student_card, 
                students.notes, 
                students.supply, 
                students.board, 
                students.add_by, 
                students.last_edit, 
                students.section, 
                students.shift, 
                students.study_type, 
                students.clear_status, 
                students.clear_by, 
                students.passcode, 
                students.passcode_date, 
                students.entry_date, 
                students.district, 
                students.tehsil, 
                students.mark_of_identification, 
                students.place_of_birth,
                courses.course_name,
                courses.course_type,
                courses.course_duration_year,
                courses.course_duration_month,
                campuses.campus_name,
                campuses.campus_code,
                classes.name,
                classes.seats,
                classes.session,
                study_type.name as study_type_name,
                shifts.name as shift_name,
                (select image from student_documents where student_documents.student_id = students.student_id and student_documents.type = "Photo" limit 1) as profile_image');
        $this->db->from('students');
        $this->db->join('courses', 'courses.course_id =students.course_id', 'inner');
        $this->db->join('classes', 'classes.class_id =students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id =campuses.campus_id', 'inner');
        $this->db->join('study_type', 'study_type.id = students.study_type', 'left');
        $this->db->join('shifts', 'shifts.id = students.shift', 'left');

        if($role != 'Admin') {
            $this->db->where_in('campuses.campus_id', $campus_ids);
        }
        $this->db->where('students.clear_status', 0);
        $query = $this->db->get()->result_array();
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Data Found Successfully!',
            'students_response'=>$query
        );
        echo json_encode($result);

    }

    public function insert_leave()
    {

        $user_id=$this->input->post('user_id');
        $description=$this->input->post('description');
        $from=$this->input->post('from_date');
        $to=$this->input->post('to_date');

        $this->db->set('leavetype',"1");
        $this->db->set('leaves_value',"1");
        $this->db->set('todate',$to);
        $this->db->set('fromdate',$from);
        $this->db->set('description',$description);
        $this->db->set('empid',$user_id);
        $this->db->set('created_by',$user_id);

        $ins = $this->db->insert('tblleaves');
        if ($ins){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Leave Request Sent Successfully!'
            );
            echo json_encode($result);
        }else{
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'0',
                'message'=>'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function Fetch_expense_request_details($user_id){

        $exp_campuses = $this->db->get_where('access', array('user_id'=>$user_id))->row()->expense_campus_ids;

        $this->db->select('*');
        $this->db->from('campuses');
        $this->db->where_in('campus_id', explode(',',$exp_campuses));
        $campuses = $this->db->get()->result_array();
        $categories = $this->db->get_where('expense_category', "has_sub = 0 and status = 'active'")->result_array();
        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Found',
            'categories'=>$categories,
            'campuses'=>$campuses
        );
        echo json_encode($result);
    }

    public function Fetch_first_expense_category($campus_id){

        $categories = $this->db->where("find_in_set($campus_id, for_campus)")->get_where('expense_category', "sub_of is NULL and status = 'active'")->result_array();
        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Found',
            'categories'=>$categories
        );
        echo json_encode($result);
    }

    public function Fetch_expense_category($category_id,$campus_id){
        $categories = $this->db->where("find_in_set($campus_id, for_campus)")->get_where('expense_category', "status = 'active' and sub_of = '$category_id'")->result_array();
        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Found',
            'categories'=>$categories
        );
        echo json_encode($result);
    }

    public function search()
    {
        $val=$this->input->post("val");
        $this->db->select('students.student_id, 
                students.course_id, 
                students.device_id, 
                students.study_campus, 
                students.first_name, 
                students.last_name, 
                students.father_name,
                students.roll_no, 
                students.plan_id, 
                students.gender,
                students.qualification, 
                students.caste, 
                students.religion, 
                students.email, 
                students.cnic, 
                students.blood_group, 
                students.date_of_birth, 
                students.registration_date, 
                students.total_fee, 
                students.city, 
                students.address, 
                students.mobile, 
                students.emergency_no, 
                students.class_id, 
                students.status,
                students.contractor_id, 
                students.contract_id,
                students.books_1, 
                students.books_2,
                students.student_card, 
                students.notes, 
                students.supply, 
                students.board, 
                students.add_by, 
                students.last_edit, 
                students.section, 
                students.shift, 
                students.study_type, 
                students.clear_status, 
                students.clear_by, 
                students.passcode, 
                students.passcode_date, 
                students.entry_date, 
                students.district, 
                students.tehsil, 
                students.mark_of_identification, 
                students.place_of_birth,
                courses.course_name,
                courses.course_type,
                courses.course_duration_year,
                courses.course_duration_month,
                campuses.campus_name,
                campuses.campus_code,
                classes.name,
                classes.seats,
                classes.session,
                study_type.name as study_type_name,
                shifts.name as shift_name,
				(select image from student_documents where student_documents.student_id = students.student_id and student_documents.type = "Photo" limit 1) as profile_image');
        $this->db->from('students');
        $this->db->join('courses', 'courses.course_id =students.course_id', 'inner');
        $this->db->join('classes', 'classes.class_id =students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id =campuses.campus_id', 'inner');
        $this->db->join('study_type', 'study_type.id = students.study_type', 'left');
        $this->db->join('shifts', 'shifts.id = students.shift', 'left');
        $this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
        $this->db->where("(students.roll_no LIKE '%".$val."%' OR students.cnic LIKE '%".$val."%' OR students.mobile LIKE '%".$val."%' OR students.emergency_no LIKE '%".$val."%' OR students.first_name LIKE '%".$val."%' OR students.last_name LIKE '%".$val."%' OR students.father_name LIKE '%".$val."%')", NULL, FALSE);
        $students = $this->db->get()->result_array();

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Found',
            'students_response'=>$students
        );
        echo json_encode($result);
    }

    public function upload()
    {

        if (!is_dir(getcwd().'/uploads')) {
            mkdir(getcwd().'/uploads', 0777);
        }

        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'uploads/';

        // set the filter image types
        $config['allowed_types'] = 'gif|jpg|png';

        //load the upload library
        $this->load->library('upload', $config);

        $this->upload->initialize($config);
        $this->upload->set_allowed_types('*');
        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('image')) {
            $student_document = '';
        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $student_document = $data['upload_data']['file_name'];
            }
        }
        $type = $this->input->post('type');
        $student_id = $this->input->post('student_id');
        $this->student->uploadDocument($student_id, $student_document, $type);
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Document uploaded Successfully!'
        );
        echo json_encode($result);
    }

    public function insert_expense_request()
    {

        $user_id                =$this->input->post('user_id');
        $title                  =$this->input->post('title');
        $description            =$this->input->post('description');
        $expense_category_id    =$this->input->post('expense_category_id');
        $campus_id              =$this->input->post('campus_id');
        $amount              =$this->input->post('amount');

        $this->db->set('expense_category_id',$expense_category_id);
        $this->db->set('campus_id',$campus_id);
        $this->db->set('title',$title);
        $this->db->set('description',$description);
        $this->db->set('amount',$amount);
        $this->db->set('created_by',$user_id);
        $this->db->set('created_at',date("Y-m-d H:i:s"));
        $ins = $this->db->insert('expense_request');
        if ($ins){
            $accesses = $this->db->select("user_id")->where("find_in_set($campus_id, expense_campus_ids)")->where('expense_approval = "1"')->get('access')->result_array();
            foreach($accesses as $access)
            {
                $exp = $this->db->get_where("users","user_id = '".$access["user_id"]."'")->row();
                if ($exp->device_id !=null && $exp->device_id !="" && $exp->status == "1")
                    $this->sendGCM("New Expense has been posted Need Approval",$exp->device_id,"New Expense Added");
            }
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Expense Request Sent Successfully!'
            );
            echo json_encode($result);
        }else{
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'0',
                'message'=>'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function get_expense_request($user_id)
    {
    $exp_campuses = $this->db->get_where('access', array('user_id' => $user_id))->row();

    $expense_array = !empty($exp_campuses->expense_campus_ids) 
        ? explode(",", $exp_campuses->expense_campus_ids) 
        : array();

    $purchase_campuses = !empty($exp_campuses->purchase_campuses) 
        ? explode(",", $exp_campuses->purchase_campuses) 
        : array();

    $this->db->select("expense_request.*,
        expense_category.name as expense_name,
        expense_category.type as expense_type,
        (select concat(first_name,' ',last_name) from users where users.user_id = expense_request.approval_first_by) as approved_user,
        (select concat(first_name,' ',last_name) from users where users.user_id = expense_request.created_by) as created_user,
        (select concat(first_name,' ',last_name) from users where users.user_id = expense_request.approval_second_by) as second_approved_user,
        campuses.campus_name as campus_id, 
        campuses.campus_id as camp_id");

    $this->db->from("expense_request");
    $this->db->join("expense_category", "expense_category.expense_category_id = expense_request.expense_category_id");
    $this->db->join("campuses", "campuses.campus_id = expense_request.campus_id");
    $this->db->where("expense_request.created_at >=", date('Y-m-d', strtotime('-20 days')) . " 00:00:00");

    $this->db->group_start();

    // Own created requests
    if ($exp_campuses->expense_add_mobile == "1") {
        $this->db->where("expense_request.created_by", $user_id);
    }

    // Purchaser campuses
    if ($exp_campuses->is_purchaser == "1" && !empty($purchase_campuses)) {

        if ($exp_campuses->expense_add_mobile == "1") {
            $this->db->or_where_in("expense_request.campus_id", $purchase_campuses);
        } else {
            $this->db->where_in("expense_request.campus_id", $purchase_campuses);
        }
    }

    // Normal expense campuses
    if (!empty($expense_array)) {

        if (
            $exp_campuses->expense_add_mobile == "1" ||
            $exp_campuses->is_purchaser == "1"
        ) {
            $this->db->or_where_in("expense_request.campus_id", $expense_array);
        } else {
            $this->db->where_in("expense_request.campus_id", $expense_array);
        }
    }

    $this->db->group_end();
    

    $this->db->order_by("expense_request.created_at", "DESC");

    $requests = $this->db->get()->result_array();

    $result = array(
        'status' => 'FOUND',
        'response_code' => '1',
        'message' => 'Found!',
        'expense_response' => $requests
    );

    echo json_encode($result);
}

    public function update_expense_approval()
    {
        $approve_first      = $this->input->post('approve_first');
        $approve_first_by   = $this->input->post('approve_first_by');
        $approve_second     = $this->input->post('approve_second');
        $approve_second_by  = $this->input->post('approve_second_by');
        $approve_first_comment  = $this->input->post('first_comment');
        $approve_second_comment  = $this->input->post('second_comment');
        $id                 = $this->input->post('id');

        $exp = $this->db->get_where("expense_request","id = '$id'")->row();
        $accesses = $this->db->select("user_id")->where("find_in_set($exp->campus_id, expense_campus_ids)")->where('expense_second_approval = "1"')->get('access')->result_array();

        $this->db->set('approval_first',$approve_first);
        $this->db->set('approval_second',$approve_second);
        $this->db->set('approval_first_by',$approve_first_by);
        $this->db->set('approval_second_by',$approve_second_by);
        if ($approve_first_comment != "")
            $this->db->set('approval_first_comment',$approve_first_comment);
        if ($approve_second_comment != "")
            $this->db->set('approval_second_comment',$approve_second_comment);
        $this->db->where('id',$id);
        $ins = $this->db->update('expense_request');
        if ($ins) {
            if ($approve_second_by < 1)
            {
                foreach ($accesses as $access) {
                    $user = $this->db->get_where("users", "user_id = '" . $access["user_id"] . "'")->row();
                    if ($user->device_id != null && $user->device_id != "" && $user->status == "1")
                        $this->sendGCM("New Expense has been Approved", $user->device_id, "New Expense Approved");
                }
                $new_user = $this->db->get_where("users", "user_id = '" . $exp->created_by . "'")->row();
                if ($new_user->device_id != null && $new_user->device_id != "" && $new_user->status == "1")
                    $this->sendGCM("New Expense has been $approve_first", $new_user->device_id, "New Expense Approved");
            }
            else{
                $new_user = $this->db->get_where("users", "user_id = '" . $exp->created_by . "'")->row();
                if ($new_user->device_id != null && $new_user->device_id != "" && $new_user->status == "1")
                    $this->sendGCM("New Expense has been $approve_second", $new_user->device_id, "New Expense Approved");
            }
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Expense updated Successfully!'
            );
            echo json_encode($result);
        }else{
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'0',
                'message'=>'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function update_expense_request()
    {

        $title                  =$this->input->post('title');
        $description            =$this->input->post('description');
        $expense_category_id    =$this->input->post('expense_category_id');
        $campus_id              =$this->input->post('campus_id');
        $amount                 =$this->input->post('amount');

        $this->db->set('expense_category_id',$expense_category_id);
        $this->db->set('campus_id',$campus_id);
        $this->db->set('title',$title);
        $this->db->set('description',$description);
        $this->db->set('amount',$amount);
        $this->db->where('id',$this->input->post('expense_id'));
        $ins = $this->db->update('expense_request');
        if ($ins){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Expense Request updated Successfully!'
            );
            echo json_encode($result);
        }else{
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'0',
                'message'=>'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function post_expensedemo()
    {

        $exp_request=$this->db->get_where('expense_request','id = "'.$this->input->post('expense_id').'"')->row();


        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'uploads/';

        // set the filter image types
        $config['allowed_types'] = 'gif|jpg|png';

        //load the upload library
        $this->load->library('upload', $config);

        $this->upload->initialize($config);

        $this->upload->set_allowed_types('*');

        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('image')) {

            $image = '';

        } else {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if ($data['upload_data']['file_name']) {
                $image = $data['upload_data']['file_name'];
            }
        }

        if ($exp_request) {
            $this->db->set('campus_id', $exp_request->campus_id);
            $this->db->set('expense_category_id', $exp_request->expense_category_id);
            $this->db->set('title', $exp_request->title);
            $this->db->set('date', date('Y-m-d'));
            $this->db->set('amount', $exp_request->amount);
            $this->db->set('purpose', $exp_request->description);
            $this->db->set('actual_date', date('Y-m-d H:i:s'));
            $this->db->set('image', $image);
            $this->db->set('payment_type', 'cash');
            $this->db->set('add_by_id', $exp_request->created_by);
            $this->db->set('request_id', $exp_request->id);
            $this->db->set('rickshaw_number', $this->input->post("rick_no"));
            $this->db->set('driver_phone', $this->input->post("rick_phone"));

            if ($this->db->insert('expenses')) {
                $this->db->set("approval_first","posted");
                $this->db->set("approval_second","posted");
                $this->db->where("id",$exp_request->id);
                $this->db->update("expense_request");
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '1',
                    'message' => 'Expense Added Successfully!'
                );
                echo json_encode($result);
            } else {
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '0',
                    'message' => 'Something Went Wrong Apply Again!'
                );
                echo json_encode($result);
            }
        }

    }

    public function post_expense()
    {

        $exp_request=$this->db->get_where('expense_request','id = "'.$this->input->post('expense_id').'"')->row();
        $cash = $this->db->get_where("petty_cash_college_wise","assign_to = '$exp_request->created_by' and petty_status = '1'")->result_array();

        if (count($cash) > 0)
        {
            $amount = pettycash_statement($cash[0]['id']);
            if ($amount >= $exp_request->amount) {

                $already_expense = $this->db->get_where("expenses","request_id = $exp_request->id")->result_array();
                if (count($already_expense)>0){
                    $this->db->set("approval_first", "posted");
                    $this->db->set("approval_second", "posted");
                    $this->db->where("id", $exp_request->id);
                    $this->db->update("expense_request");
                    $result = array(
                        'status' => 'FOUND',
                        'response_code' => '1',
                        'message' => 'Expense Added Successfully!'
                    );
                }
                else {
                    //load the helper
                    $this->load->helper('form');

                    //Configure
                    //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
                    $config['upload_path'] = 'uploads/';

                    // set the filter image types
                    $config['allowed_types'] = 'gif|jpg|png';

                    //load the upload library
                    $this->load->library('upload', $config);

                    $this->upload->initialize($config);

                    $this->upload->set_allowed_types('*');

                    $data['upload_data'] = '';

                    //if not successful, set the error message
                    if (!$this->upload->do_upload('image')) {

                        $image = '';

                    } else {
                        //else, set the success message
                        $data['upload_data'] = $this->upload->data();
                        if ($data['upload_data']['file_name']) {
                            $image = $data['upload_data']['file_name'];
                        }
                    }

                    if ($exp_request) {
                        $this->db->set('campus_id', $exp_request->campus_id);
                        $this->db->set('expense_category_id', $exp_request->expense_category_id);
                        $this->db->set('title', $exp_request->title);
                        $this->db->set('date', date('Y-m-d'));
                        $this->db->set('amount', $exp_request->amount);
                        $this->db->set('purpose', $exp_request->description);
                        $this->db->set('actual_date', date('Y-m-d H:i:s'));
                        $this->db->set('image', $image);
                        $this->db->set('payment_type', 'cash');
                        $this->db->set('add_by_id', $exp_request->created_by);
                        $this->db->set('rickshaw_number', $this->input->post("rick_no"));
                        $this->db->set('driver_phone', $this->input->post("rick_phone"));
                        $this->db->set('request_id', $exp_request->id);
                        $this->db->set('approved_status', "1");

                        if ($this->db->insert('expenses')) {

                            $this->db->set("approval_first", "posted");
                            $this->db->set("approval_second", "posted");
                            $this->db->where("id", $exp_request->id);
                            $this->db->update("expense_request");
                            $result = array(
                                'status' => 'FOUND',
                                'response_code' => '1',
                                'message' => 'Expense Added Successfully!'
                            );
                            echo json_encode($result);
                        } else {
                            $result = array(
                                'status' => 'FOUND',
                                'response_code' => '0',
                                'message' => 'Something Went Wrong Apply Again!'
                            );
                            echo json_encode($result);
                        }
                    }
                }
            }else {
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '0',
                    'message' => "Your PettyCash is $amount and Your Expense Amount is $exp_request->amount"
                );
                echo json_encode($result);
            }
        }

        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'You have no PettyCash'
            );
            echo json_encode($result);
        }
    }

    function sendGCM($message, $id,$title) {

        $url = 'https://fcm.googleapis.com/fcm/send';
        $api_key = 'AAAAFPFaubY:APA91bGaMDgdgA3O8XG_QA6ZJBBPJ_p-eLFW4AgS_S4wDm8-zQWVCy1B_G6fvkci5DOP-06seAlv1fU-DLsX5MujC7Rce0diZWq1GbrU5c0GiCt0rDIqFFX9MuiNpRIAHZ62__sbRhmP';
        $fields = array (
            'to'        => $id,
            // 'registration_ids' => array (
            //         $device_array
            // ),
            'data' => array (
                "title" => $title,
                "message" => $message
            )
        );


        //header includes Content type and api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='.$api_key
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
    }

    public function insert_adv_expense_request() {

        $user_id = $this->input->post('user_id');
        $cash = $this->db->get_where("petty_cash_college_wise","assign_to = '$user_id' and petty_status = '1'")->result_array();

        if (count($cash) > 0) {
            $amount = pettycash_statement($cash[0]['id']);
            if ($amount > $this->input->post('amount')) {
                //load the helper
                $this->load->helper('form');
                //Configure
                //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
                $config['upload_path'] = 'uploads/';
                // set the filter image types
                $config['allowed_types'] = 'gif|jpg|png';
                //load the upload library
                $this->load->library('upload', $config);

                $this->upload->initialize($config);
                $this->upload->set_allowed_types('*');
                $data['upload_data'] = '';

                //if not successful, set the error message
                if (!$this->upload->do_upload('image')) {
                    $image = '';
                } else {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if ($data['upload_data']['file_name']) {
                        $image = $data['upload_data']['file_name'];
                    }
                }

                $vehicle_no = $this->input->post('vehicle_no');
                $driver_phone = $this->input->post('driver_phone');
                $flax_sr_no = $this->input->post('flax_sr_no');
                $amount = $this->input->post('amount');
                $latitude = $this->input->post('latitude');
                $longitude = $this->input->post('longitude');
                $weather = $this->input->post('weather');
                $created_by = $this->input->post('user_id');
                $campus_id = $this->input->post('campus_id');

                $this->db->set('vehicle_no', $vehicle_no);
                $this->db->set('driver_phone', $driver_phone);
                $this->db->set('flax_sr_no', $flax_sr_no);
                $this->db->set('amount', $amount);
                $this->db->set('image', $image);
                $this->db->set('latitude', $latitude);
                $this->db->set('longitude', $longitude);
                $this->db->set('weather', $weather);
                $this->db->set('campus_id', $campus_id);
                $this->db->set('created_by', $created_by);
                $this->db->set('created_at', date("Y-m-d H:i:s"));
                if ($vehicle_no == "Flax" || $vehicle_no == "Banner")
                    $this->db->set('status', "approved");
                $ins = $this->db->insert('advertisement_expenses');
                if ($ins) {
                    $exp_adv_id = $this->db->insert_id();
                    if ($vehicle_no == "Flax" || $vehicle_no == "Banner") {

                    }else {
                        $this->db->set('campus_id', $campus_id);
                        $this->db->set('expense_category_id', "120");
                        $this->db->set('title', "Advertisement Expense for Ricksaw");
                        $this->db->set('date', date('Y-m-d'));
                        $this->db->set('amount', $amount);
                        $this->db->set('purpose', "Advertisement Rickshaw Flex");
                        $this->db->set('actual_date', date('Y-m-d H:i:s'));
                        $this->db->set('image', $image);
                        $this->db->set('payment_type', 'cash');
                        $this->db->set('add_by_id', $created_by);
                        $this->db->set('rickshaw_number', $vehicle_no);
                        $this->db->set('driver_phone', $driver_phone);
                        $this->db->set('adv_request_id', $exp_adv_id);
                        $this->db->set('approved_status', "1");
                        $this->db->insert('expenses');
                    }

                    $accesses = $this->db->select("user_id")->where("find_in_set($campus_id, expense_campus_ids)")->where('expense_advertisement_approval = "1"')->get('access')->result_array();
                    foreach ($accesses as $access) {
                        $exp = $this->db->get_where("users", "user_id = '" . $access["user_id"] . "'")->row();
                        if ($exp->device_id != null && $exp->device_id != "" && $exp->status == "1")
                            $this->sendGCM("New Expense has been posted Need Approval", $exp->device_id, "New Expense Added");
                    }
                    $result = array(
                        'status' => 'FOUND',
                        'response_code' => '1',
                        'message' => 'Expense Request Sent Successfully!'
                    );
                    echo json_encode($result);
                } else {
                    $result = array(
                        'status' => 'FOUND',
                        'response_code' => '0',
                        'message' => 'Something Went Wrong Apply Again!'
                    );
                    echo json_encode($result);
                }
            }
            else {
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '0',
                    'message' => "Your PettyCash is $amount and Your Expense Amount is $amount"
                );
                echo json_encode($result);
            }
        }

        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'You have no PettyCash'
            );
            echo json_encode($result);
        }
    }

    public function find_rickshaw()
    {
        $date = date('Y-m-d',strtotime("-30 days"));
        $val=$this->input->post("val");
        $this->db->select('*');
        $this->db->from('advertisement_expenses');
        $this->db->where("vehicle_no = '$val' and status != 'rejected' and created_at > '$date 00:00:00'");
        $students = $this->db->get()->result_array();

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Found',
            'expenses'=>$students
        );
        echo json_encode($result);
    }

    public function get_adv_expense_request($user_id)
    {
        $exp_campuses = $this->db->get_where('access', array('user_id'=>$user_id))->row();
        $expense_array = explode(",",$exp_campuses->expense_campus_ids);
        $this->db->select("advertisement_expenses.*,concat(users.first_name,' ',users.last_name) as created_user, campuses.campus_name as campus_name")
            ->join("users","users.user_id = advertisement_expenses.created_by")
            ->join("campuses","campuses.campus_id = advertisement_expenses.campus_id")
            ->order_by("advertisement_expenses.created_at","DESC");
        $this->db->where("created_at >=",date('Y-m-d', strtotime('-7 days'))." 00:00:00");
        if ($exp_campuses->expense_advertisement_create == "1")
            $requests = $this->db->get_where('advertisement_expenses',"advertisement_expenses.created_by = '$user_id'")->result_array();
        else  {
            $this->db->where_in("advertisement_expenses.campus_id" ,$expense_array);
            $requests = $this->db->get("advertisement_expenses")->result_array();
        }

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'expense_advertisement_response'=>$requests
        );
        echo json_encode($result);
    }

    public function update_adv_expense_request()
    {
        $id         =$this->input->post('id');
        $status     =$this->input->post('status');
        $user_id    =$this->input->post('user_id');

        $this->db->set('status',$status);
        $this->db->set('approved_by',$user_id);
        $this->db->where('id',$id);
        $ins = $this->db->update('advertisement_expenses');
        if ($ins){
            if ($status == "rejected")
            {
                $exp = $this->db->get_where("expenses","adv_request_id = '$id'")->result_array();
                if (count($exp) >0)
                {
                    $this->db->set('approved_status', "2");
                    $this->db->where('expense_id', $exp[0]['expense_id']);
                    $this->db->update('expenses');

                    $this->db->set('expense_id', $exp[0]['expense_id']);
                    $this->db->set('amount', $exp[0]['amount']);
                    $this->db->set('reverse_by', $user_id);
                    $this->db->set('created_at',date('Y-m-d H:i:s'));
                    $this->db->insert('cash_reversal');
                }
            }
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Expense Request updated Successfully!'
            );
            echo json_encode($result);
        }else{
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'0',
                'message'=>'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function Fetch_cash_request_details($user_id){

        $petty_cash_users = $this->db->select('users.*')
            ->join('users','users.user_id = petty_cash_college_wise.assign_to')
            ->where("users.user_id != '$user_id'")
            ->get_where('petty_cash_college_wise', array('petty_cash_college_wise.petty_status'=>1))
            ->result_array();

        $this->db->select('users.*');
        $this->db->from('access');
        $this->db->join('users','users.user_id = access.user_id');
        $this->db->where('access.is_carrier', "1");
        $this->db->where('users.role !=', "Admin");
        $carrier_users = $this->db->get()->result_array();

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Found',
            'petty_users'=>$petty_cash_users,
            'carrier_users'=>$carrier_users
        );
        echo json_encode($result);
    }

    public function insert_cash_request()
    {
        $user_id        =$this->input->post('user_id');
        $date           =$this->input->post('date');
        $reason         =$this->input->post('reason');
        $carrier        =$this->input->post('carrier');
        $require_from   =$this->input->post('require_from');
        $amount         =$this->input->post('amount');

        $petty_cash_users = $this->db
            ->get_where('petty_cash_college_wise', array('petty_status'=>1,'assign_to'=>$user_id))
            ->result_array();

        if (count($petty_cash_users) > 0) {

            $this->db->set('reason', $reason);
            $this->db->set('created_by', $user_id);
            $this->db->set('carrier', $carrier);
            $this->db->set('require_from', $require_from);
            $this->db->set('amount', $amount);
            $this->db->set('require_date', $date);
            $this->db->set('created_at', date("Y-m-d H:i:s"));
            $ins = $this->db->insert('cash_requests');
            if ($ins) {
                $exp = $this->db->get_where("users", "user_id = '$require_from'")->row();
                if ($exp->device_id != null && $exp->device_id != "" && $exp->status == "1")
                    $this->sendGCM("New Cash Request has been posted Need your Approval", $exp->device_id, "New Cash Request Added");

                $exp = $this->db->get_where("users", "user_id = '" . $carrier . "'")->row();
                if ($exp->device_id != null && $exp->device_id != "")
                    $this->sendGCM("New Carrier Request has been posted", $exp->device_id, "New Cash Request");
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '1',
                    'message' => 'Cash Request Sent Successfully!'
                );
                echo json_encode($result);
            } else {
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '0',
                    'message' => 'Something Went Wrong Apply Again!'
                );
                echo json_encode($result);
            }
        }
        else{
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'0',
                'message'=>'Please Activate PettyCash!'
            );
            echo json_encode($result);
        }
    }

    public function update_cash_request()
    {
        $date           =$this->input->post('date');
        $reason         =$this->input->post('reason');
        $carrier        =$this->input->post('carrier');
        $amount         =$this->input->post('amount');
        $require_from   =$this->input->post('require_from');

        $this->db->set('reason',$reason);
        $this->db->set('carrier',$carrier);
        $this->db->set('require_from',$require_from);
        $this->db->set('amount',$amount);
        $this->db->set('require_date',$date);

        $this->db->where('id',$this->input->post('cash_id'));
        $ins = $this->db->update('cash_requests');

        if ($ins){
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Cash Request updated Successfully!'
            );
            echo json_encode($result);
        }else{
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'0',
                'message'=>'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function get_cash_request()
    {
        $from_date = $this->input->post("from_date");
        $to_date = $this->input->post("to_date");
        $user_id = $this->input->post("user_id");

        $this->db->select("cash_requests.*,
        (select concat(first_name,' ',last_name) as approved_user from users where users.user_id = cash_requests.given_by) as approved_user,
        (select concat(first_name,' ',last_name) as created_by from users where users.user_id = cash_requests.created_by) as created_user,
        (select concat(first_name,' ',last_name) as created_by from users where users.user_id = cash_requests.require_from) as from_user,
        (select concat(first_name,' ',last_name) as carrier from users where users.user_id = cash_requests.carrier) as carrier_user,
        (IF(cash_requests.given_by = '$user_id' and cash_requests.status = 'received','sent',cash_requests.status)) as status")
            ->order_by("cash_requests.created_at","DESC");
        $this->db->where("(created_by = '$user_id' or require_from = '$user_id' or carrier = '$user_id')");
        $requests = $this->db->get_where('cash_requests',array('created_at >='=>$from_date." 00:00:00",'created_at <='=>$to_date." 23:59:59"))

            ->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'cash_response'=>$requests
        );
        echo json_encode($result);
    }

    public function update_cash_approval()
    {
        $status     = $this->input->post('status');
        $user_id    = $this->input->post('user_id');
        $id         = $this->input->post('id');
        $amount     = $this->input->post('given_amount');

        $exp_request=$this->db->get_where('cash_requests','id = "'.$id.'"')->row();
        $cash = $this->db->get_where("petty_cash_college_wise","assign_to = '$user_id' and petty_status = '1'")->result_array();

        $user_amount = pettycash_statement($cash[0]['id']);
        if ($user_amount >= $amount) {

            $exp = $this->db->get_where("cash_requests", "id = '$id'")->row();
            $fromaccount = $this->db->get_where("petty_cash_college_wise", "assign_to = '$user_id'")->row()->id;
            $to_account = $this->db->get_where("petty_cash_college_wise", "assign_to = '$exp->created_by'")->row()->id;
            $user = $this->db->get_where("users", "user_id = '$user_id'")->row();
            $carrier = $this->db->get_where("users", "user_id = '$exp->carrier'")->row();
            $carrier = $carrier->first_name . ' ' . $carrier->last_name;

            $this->db->set('status', $status);
            $this->db->set('given_cash', $amount);
            $this->db->set('given_by', $user_id);
            $this->db->set('given_date', date("Y-m-d H:i:s"));
            $this->db->where('id', $id);
            $ins = $this->db->update('cash_requests');
            if ($ins) {
                if ($status == 'on-the-way') {
                    $p_exp = $this->db->get_where("cash_requests", "id = '$id'")->row();
                    if ($p_exp->petty_cash_id == NULL || $p_exp->petty_cash_id == "") {
                        $this->db->set('debit_credit', 'D');
                        $this->db->set('amount_given', $amount);
                        $this->db->set('from_pettycash_id', $fromaccount);
                        $this->db->set('to_pettycash_id', $to_account);
                        $this->db->set('transaction_pettycash_account', $to_account);
                        $this->db->set('status', '1');
                        $this->db->set('transaction_by', $user->first_name . ' ' . $user->last_name);
                        $this->db->set('created_at', date('Y-m-d H:i:s'));
                        $this->db->set('reason', "Sent with carrier " . $carrier . " " . $exp->reason);
                        $this->db->set('trans_status', 'pending');
                        $this->db->set('proof_image', "");
                        $this->db->insert('petty_cash_history');

                        $this->db->set('petty_cash_id', $this->db->insert_id());
                        $this->db->where('id', $id);
                        $ins = $this->db->update('cash_requests');

                        $this->db->set('debit_credit', 'C');
                        $this->db->set('amount_given', $amount);
                        $this->db->set('from_pettycash_id', $fromaccount);
                        $this->db->set('to_pettycash_id', $to_account);
                        $this->db->set('transaction_pettycash_account', $fromaccount);
                        $this->db->set('status', '1');
                        $this->db->set('reason', "Sent with carrier " . $carrier . " " . $exp->reason);
                        $this->db->set('proof_image', "");
                        $this->db->set('transaction_by', $user->first_name . ' ' . $user->last_name);
                        $this->db->set('created_at', date('Y-m-d H:i:s'));
                        $this->db->insert('petty_cash_history');
                    }
                }

                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '1',
                    'message' => 'Expense updated Successfully!'
                );
                echo json_encode($result);
            }
            else {
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '0',
                    'message' => 'Something Went Wrong Apply Again!'
                );
                echo json_encode($result);
            }
        }
        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => "Your PettyCash is $user_amount and Your Require Amount is $amount"
            );
            echo json_encode($result);
        }
    }

    public function update_cash_received()
    {

        $id         = $this->input->post('id');
        $exp = $this->db->get_where("cash_requests","id = '$id'")->row();

        $this->db->set('status',"received");
        $this->db->where('id',$id);
        $ins = $this->db->update('cash_requests');
        if ($ins) {
            $this->db->set('trans_status',"completed");
            $this->db->where('id',$exp->petty_cash_id);
            $this->db->update('petty_cash_history');

            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Expense updated Successfully!'
            );
            echo json_encode($result);
        }
        else{
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'0',
                'message'=>'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function pettycash_statement()
    {

        $pettycashid = $this->input->post("user_id");
        $check_record = $this->db->get_where('petty_cash_college_wise', array('assign_to'=>$pettycashid))->row();

        if(@$this->input->post('from_date'))  {
            $data['from_date'] = $this->input->post('from_date');
        }
        else  {
            $data['from_date'] = date('Y/m/1');
        }

        if(@$this->input->post('to_date')) {
            $data['to_date'] = $this->input->post('to_date');
        }
        else {
            $data['to_date'] = date('Y/m/d');
        }

        $data['check_record'] = $check_record;
        $data['openbalance']=$check_record->opening_balance;

        $this->db->select('sum(amount) as amount');
        $this->db->from('expenses');
        $this->db->where('add_by_id = "'.$check_record->assign_to.'"  and actual_date >= "'.$check_record->given_date.'"  and actual_date < "'.$data['from_date'].'" and paid_type ="cash" and expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)');
        $expenseamount = $this->db->get()->row();

        $this->db->select('sum(cash_reversal.amount) as amount');
        $this->db->from('cash_reversal');
        $this->db->join('expenses','expenses.expense_id = cash_reversal.expense_id');
        $this->db->where('expenses.add_by_id = "'.$check_record->assign_to.'"  and cash_reversal.created_at >= "'.$check_record->given_date.'"  and cash_reversal.created_at < "'.$data['from_date'].' 23:59:59"');
        $expensereverseamount = $this->db->get()->row();

        $this->db->select('id as trans_id,"receive from" as detail,"trans" as trans_type,amount_given as amount,"" as expstatus, debit_credit,created_at,"" as image,transaction_by as trans_by ');
        $this->db->from('petty_cash_history');
        $this->db->where('transaction_pettycash_account = "'.$check_record->id.'" and created_at < "'.$data['from_date'].'" ');
        $trans_petty_cash = $this->db->get()->result_array();

        $debit=0;
        $credit=0;

        foreach ($trans_petty_cash as $tran)
        {
            if ($tran['debit_credit']  == 'C' )  {
                $credit+=$tran['amount'];
            }
            else    {
                $debit+=$tran['amount'];
            }
        }

        $data['openbalance'] = ($data['openbalance']+$debit+$expensereverseamount->amount)-$credit-$expenseamount->amount;

        $this->db->select('"" as balance,expenses.expense_id as trans_id,expenses.user_id as user_id,concat(expense_category.name," - ",expenses.title," - ",expenses.purpose," - ",campuses.campus_name," - ",expenses.date) as detail,"exp" as trans_type,expenses.amount as amount,"C" as debit_credit,expenses.approved_status as expstatus,expenses.actual_date as created_at,"" as reason,expenses.image,expenses.add_by as trans_by');
        $this->db->from('expenses');
        $this->db->join('expense_category','expense_category.expense_category_id  = expenses.expense_category_id','left');
        $this->db->join('users','users.user_id = expenses.add_by_id','left');
        $this->db->join('campuses','campuses.campus_id = expenses.campus_id','left');
        $this->db->where('expenses.add_by_id = "'.$check_record->assign_to.'"  and expenses.actual_date >= "'.$data['from_date'].' 00:00:00" and paid_type ="cash"   and expenses.actual_date <= "'.$data['to_date'].' 23:59:59"   and expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)');
        $expenses = $this->db->get()->result_array();

        $this->db->select('"" as balance,id as trans_id,expenses.user_id as user_id,concat("Reversal against ",expense_category.name," - ",expenses.title," - ",expenses.purpose," - ",campuses.campus_name," - ",expenses.date) as detail,"exp" as trans_type,cash_reversal.amount as amount,"D" as debit_credit,"Reversal" as expstatus,cash_reversal.created_at as created_at,"Reversal against Expense" as reason,expenses.image,expenses.add_by as trans_by');
        $this->db->from('cash_reversal');
        $this->db->join('expenses','expenses.expense_id = cash_reversal.expense_id','left');
        $this->db->join('expense_category','expense_category.expense_category_id  = expenses.expense_category_id','left');
        $this->db->join('users','users.user_id = expenses.add_by_id','left');
        $this->db->join('campuses','campuses.campus_id = expenses.campus_id','left');
        $this->db->where('expenses.add_by_id = "'.$check_record->assign_to.'" and cash_reversal.created_at >="'.date("Y-m-d",strtotime($data['from_date'])).' 23:59:59" and cash_reversal.created_at <="'.date("Y-m-d",strtotime($data['to_date'])).' 23:59:59"');
//        and cash_reversal.created_at >= "'.$check_record->given_date.'"
        $expensereverseamount = $this->db->get()->result_array();


        $this->db->select('"" as balance,id as trans_id,"receive from" as detail,"0" as user_id,"trans" as trans_type,amount_given as amount,"" as expstatus, debit_credit,created_at,proof_image as image,reason,transaction_by as trans_by ');
        $this->db->from('petty_cash_history');
        $this->db->where('transaction_pettycash_account = "'.$check_record->id.'"  and created_at >= "'.$data['from_date'].' 00:00:00" and  created_at <= "'.$data['to_date'].'  23:59:59"');
        $trans_petty_cash = $this->db->get()->result_array();

        $data['Pettycashs']=array_merge($expenses,$trans_petty_cash);
        $data['Pettycashs']=array_merge($data['Pettycashs'],$expensereverseamount);

        $debit=0;
        $credit=0;
        $balance=0;

        foreach($data['Pettycashs'] as $key=>$petty)
        {

            if($petty['user_id'] != '0' && $petty['user_id'] != NULL )
            {
                $userdata=$this->db->get_where('users','user_id = "'.$petty['user_id'].'"')->row();
                $data['Pettycashs'][$key]['detail'] = $petty['detail'].' '.$userdata->first_name.' '.$userdata->last_name;
            }
        }

        array_multisort(array_column($data['Pettycashs'], 'created_at'),  SORT_ASC,
            $data['Pettycashs']);

        $debit+=$data['openbalance'];
        $balance+=$data['openbalance'];
        foreach ($data['Pettycashs'] as $key=>$Pettycash) {
            if ($Pettycash['trans_type'] == 'trans') {
                $this->db->select('*');
                $this->db->from('petty_cash_history');
                $this->db->where('id', $Pettycash['trans_id']);
                $tran = $this->db->get()->result_array();

                $transtext = '';


                if ($tran[0]['debit_credit'] == 'C' && $tran[0]['to_pettycash_id'] != NULL) {
                    $transtext .= "Sent to Petty cash account ";

                    $this->db->select('*');
                    $this->db->from('petty_cash_college_wise');
                    $this->db->join('users', 'users.user_id = petty_cash_college_wise.assign_to', 'inner');
                    $this->db->where('id', $tran[0]['to_pettycash_id']);
                    $to_petty = $this->db->get()->result_array();

                    $transtext .= $to_petty[0]['first_name'] . ' ' . $to_petty[0]['last_name'];

                } elseif ($tran[0]['debit_credit'] == 'C' && $tran[0]['to_account'] != NULL) {
                    $transtext .= "Sent to main account ";

                    $this->db->select('*');
                    $this->db->from('accounts');
                    $this->db->where('id', $tran[0]['to_account']);
                    $to_petty = $this->db->get()->result_array();

                    $transtext .= $to_petty[0]['account_title'] . ' ' . $to_petty[0]['account_name'];
                }


                if ($tran[0]['debit_credit'] == 'D' && $tran[0]['from_pettycash_id'] != NULL) {
                    $transtext .= "Receive from Petty cash account ";

                    $this->db->select('*');
                    $this->db->from('petty_cash_college_wise');
                    $this->db->join('users', 'users.user_id = petty_cash_college_wise.assign_to', 'inner');
                    $this->db->where('id', $tran[0]['from_pettycash_id']);
                    $from_petty = $this->db->get()->result_array();

                    $transtext .= $from_petty[0]['first_name'] . ' ' . $from_petty[0]['last_name'];

                } elseif ($tran[0]['debit_credit'] == 'D' && $tran[0]['from_account'] != NULL) {
                    $transtext .= "Receive from main account ";

                    $this->db->select('*');
                    $this->db->from('accounts');
                    $this->db->where('id', $tran[0]['from_account']);
                    $from_petty = $this->db->get()->result_array();
                    $transtext .= $from_petty[0]['account_title'] . ' ' . $from_petty[0]['account_name'];
                }

                $transtext .= ' - ' . $Pettycash['reason'];
                $data['Pettycashs'][$key]['detail'] = $transtext;

            }
            if ($Pettycash ['debit_credit'] == 'D') {
                $debit += $Pettycash ['amount'];
                $balance += $Pettycash ['amount'];
                $data['Pettycashs'][$key]['balance'] = $balance;
            }
            if ($Pettycash ['debit_credit'] == 'C') {
                $credit += $Pettycash ['amount'];
                $balance -= $Pettycash ['amount'];
                $data['Pettycashs'][$key]['balance'] = $balance;
            }
        }
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'opening_balance'=>$data['openbalance'],
            'debit'=>$debit,
            'credit'=>$credit,
            'balance'=>$balance,
            'statement'=>$data['Pettycashs']
        );
        echo json_encode($result);
    }

    public function struckofstudentview()
    {
        $studentid = $this->input->post("student_id");

//            $this->db->select('students.*,campuses.campus_name, courses.course_name ,classes.name as class_name,machine_data.machine_id');
//            $this->db->from('students');            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
//            $this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
//            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
//            $this->db->join('courses', 'courses.course_id=students.course_id', 'left');
//            $this->db->where("(students.student_id = '".$studentid."')", NULL, FALSE);
//            $data['students'] = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where("(payments.student_id = '".$studentid."' and payments.paid = '1')", NULL, FALSE);
        $paid=$this->db->get()->result_array();
        $endstringpaid = "";
        $total=0;
        foreach ($paid as $paymentpayed){
            $endstringpaid.="Rs ".$paymentpayed['actual_amount']." paid on ".$paymentpayed['paid_date'].' <br> ';
            $total+=$paymentpayed['actual_amount'];
        }

        $endstringpaid.="<br> <strong>Total Paid </strong> = <strong>".$total."</strong>";
        $data['paid_fee'] = $endstringpaid;

        $total=0;
        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where("(payments.student_id = '".$studentid."' and payments.paid = '0')", NULL, FALSE);
        $unpaid = $this->db->get()->result_array();

        $endstringunpaid = "";
        foreach ($unpaid as $paymentunpayed){
            $endstringunpaid.="Rs ".$paymentunpayed['amount']." Unpaid on ".$paymentunpayed['dead_line'].' <br> ';
            $total+=$paymentunpayed['amount'];
        }

        $endstringunpaid.="<br> <strong>Total UnPaid </strong>  = <strong>".$total."</strong>";
        $data['unpaid_fee'] = $endstringunpaid;

        $this->db->select('struckofdetails_students.*,concat(users.first_name," ",users.last_name) as contact_by');
        $this->db->from('struckofdetails_students');
        $this->db->join("users","users.user_id = struckofdetails_students.created_by");
        $this->db->where("(struckofdetails_students.student_id = '".$studentid."')", NULL, FALSE);

        $data['struckofdata'] = $this->db->get()->result_array();
        $data['studentid'] = $studentid;

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'paid_fee'=>$data['paid_fee'],
            'unpaid_fee'=>$data['unpaid_fee'],
            'response_struckofdata'=>$data['struckofdata']
        );
        echo json_encode($result);
    }

    public function get_vendors()
    {
        $requests = $this->db->select('vendors.*,concat(users.first_name," ",users.last_name) as created_by_user')->join("users","users.user_id = vendors.created_by")->get('vendors')->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'vendors_response'=>$requests
        );
        echo json_encode($result);
    }

    public function insert_vendor()
    {
        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'uploads/';

        // set the filter image types
        $config['allowed_types'] = 'gif|jpg|png';

        //load the upload library
        $this->load->library('upload', $config);

        $this->upload->initialize($config);

        $this->upload->set_allowed_types('*');

        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('image')) {

            $image = '';

        } else {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if ($data['upload_data']['file_name']) {
                $image = $data['upload_data']['file_name'];
            }
        }
        $name        =$this->input->post('name');
        $phone         =$this->input->post('phone');
        $address          =$this->input->post('address');
        $user_id          =$this->input->post('user_id');

        $records = $this->db->get_where("vendors","phone = '$phone'")->result_array();
        if (count($records) < 1) {
            $this->db->set('name', $name);
            $this->db->set('phone', $phone);
            $this->db->set('address', $address);
            $this->db->set('image', $image);
            $this->db->set('created_by', $user_id);
            $this->db->set('created_at', date("Y-m-d H:i:s"));
            $ins = $this->db->insert('vendors');
            if ($ins) {
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '1',
                    'message' => 'Vendor Request Sent Successfully!'
                );
                echo json_encode($result);
            } else {
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '0',
                    'message' => 'Something Went Wrong Apply Again!'
                );
                echo json_encode($result);
            }
        }else{
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => "Vendor already Found with Name : ".$records[0]['name']
            );
            echo json_encode($result);
        }
    }

    public function update_vendor()
    {
        $name        =$this->input->post('name');
        $phone       =$this->input->post('phone');
        $address     =$this->input->post('address');
        $user_id     =$this->input->post('vendor_id');

        $records = $this->db->get_where("vendors","phone = '$phone' and id != $user_id")->result_array();
        if (count($records) < 1) {
            $this->db->set('name', $name);
            $this->db->set('phone', $phone);
            $this->db->set('address', $address);
            $this->db->where('id', $user_id);
            $ins = $this->db->update('vendors');
            if ($ins) {
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '1',
                    'message' => 'Vendor Request Sent Successfully!'
                );
                echo json_encode($result);
            } else {
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '0',
                    'message' => 'Something Went Wrong Apply Again!'
                );
                echo json_encode($result);
            }
        }else{
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => "Vendor already Found with Name : ".$records[0]['name']
            );
            echo json_encode($result);
        }
    }

    public function product_placement_report()
    {
        $requests = $this->db
            ->select('product_names.*,sum(product_quantity) as total_products,sum(remaining_quantity) as remaining_quantity,sum(consume) as consumed_quantity,sum(sale_quantity) as sale_quantity,sum(move_quantity) as move_quantity')
            ->from('products')
            ->join('campuses','campuses.campus_id=products.campus_id','inner')
            ->join("product_names","product_names.product_name_id = products.product_name_id","inner")
            ->group_by("products.product_name_id")
            ->get()->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'products_response'=>$requests
        );
        echo json_encode($result);
    }

    public function product_campus_placement_report()
    {
        $requests = $this->db
            ->select('campuses.campus_name,products.campus_id,product_names.*,products.campus_id,sum(product_quantity) as total_products,sum(remaining_quantity) as remaining_quantity,sum(consume) as consumed_quantity,sum(sale_quantity) as sale_quantity,sum(move_quantity) as move_quantity')
            ->from('products')
            ->join('campuses','campuses.campus_id=products.campus_id','inner')
            ->join("product_names","product_names.product_name_id = products.product_name_id","inner")
            ->group_by("products.product_name_id,products.campus_id")
            ->get()->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'products_response'=>$requests
        );
        echo json_encode($result);
    }

    public function get_campuses()
    {
        $requests = $this->db->get('campuses')->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'response_campuses'=>$requests
        );
        echo json_encode($result);
    }

    public function all_campus_products()
    {

        $campus_id = $this->input->post('campus_id');
        $product_name_id = $this->input->post('product_id');
        $array = array(
            'products.campus_id'=>$campus_id,
            'products.product_name_id'=>$product_name_id
        );

        $this->db->select('campuses.campus_name,products.campus_id,rooms.room_name,subrooms.subroom_name,products.product_id,products.remaining_quantity,product_names.*,product_quantity as total_products,consume as consumed_quantity,products.purchase_slip,concat(users.first_name," ",users.last_name) as responsible_user,sale_quantity as sale_quantity');
        $this->db->from('products');
        $this->db->join('users','users.user_id=products.reponsilble_user_id','inner');
        $this->db->join('campuses','campuses.campus_id=products.campus_id','inner');
        $this->db->join('product_names','products.product_name_id=product_names.product_name_id','left');
        $this->db->join('rooms','rooms.room_id=products.room_id','left');
        $this->db->join('subrooms','subrooms.subroom_id=products.subroom_id','left');
        $this->db->where($array);
        $products = $this->db->get()->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'products_response'=>$products
        );
        echo json_encode($result);
    }

    public function insert_consume_request()
    {
        $product_id        =$this->input->post('product_id');
        $quantity          =$this->input->post('quantity');
        $user_id          =$this->input->post('user_id');
        $exp = $this->db->get_where("products","product_id = '$product_id'")->row();

        $this->db->set('from_product_id',$product_id);
        $this->db->set('credit',$quantity);
        $this->db->set('type',"consume");
        $this->db->set('transaction_by',$user_id);
        $this->db->set('created_at',date("Y-m-d H:i:s"));
        $ins = $this->db->insert('inventory_transactions');
        if ($ins){
            $this->db->set('remaining_quantity', 'remaining_quantity -' . $quantity . '', false);
            $this->db->set('consume', 'consume +' . $quantity . '', false);
            $this->db->where('product_id', $product_id);
            $this->db->update('products');
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Cash Request Sent Successfully!'
            );
            echo json_encode($result);
        }else{
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'0',
                'message'=>'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function get_move_data_request()
    {
        $campuses = $this->db->get('campuses')->result_array();
        $rooms = $this->db->get('rooms')->result_array();
        $subrooms = $this->db->get('subrooms')->result_array();
        $users = $this->db->get('users')->result_array();
        $products = $this->db->get_where('product_names','has_sub = 0')->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'response_campuses'=>$campuses,
            'response_rooms'=>$rooms,
            'response_subrooms'=>$subrooms,
            'response_users'=>$users,
            'products_response'=>$products
        );
        echo json_encode($result);
    }

    public function move_inventory_product()
    {
        $user_id            =$this->input->post('user_id');
        $campus_id          =$this->input->post('campus_id');
        $reason             =$this->input->post('reason');
        $room_id            =$this->input->post('room_id');
        $subroom_id         =$this->input->post('subroom_id');
        $product_id         =$this->input->post('product_id');
        $product_quantity   =$this->input->post('qty');

        $product = $this->db->get_where("products","product_id = '$product_id'")->row();
        $user = $this->db->get_where("users","user_id = '$user_id'")->row();

        $this->db->set('campus_id',$campus_id);
        $this->db->set('room_id',$room_id);
        $this->db->set('subroom_id',$subroom_id);
        $this->db->set('product_name_id',$product->product_name_id);
        $this->db->set('product_quantity',$product_quantity);
        $this->db->set('remaining_quantity',$product_quantity);
        $this->db->set('product_guarantee',$product->product_guarantee);
        $this->db->set('product_guarantee_start_date',$product->product_guarantee_start_date);
        $this->db->set('product_guarantee_end_date',$product->product_guarantee_end_date);
        $this->db->set('remarks',$reason);
        $this->db->set('user_id',$user_id);
        $this->db->set('purchase_slip',$product->purchase_slip);
        $this->db->set('reponsilble_user_id',$product->reponsilble_user_id);
        $this->db->set('date',$product->date);
        $this->db->set('add_by',$user->first_name." ".$user->last_name);
        $this->db->set('last_edit',"");
        $this->db->set('clear_by',$user->first_name." ".$user->last_name);
        $this->db->set('status',"1");
        $this->db->set('po_no',$product->po_no);

        $this->db->insert('products');
        $to_id = $this->db->insert_id();

        $this->db->set('remaining_quantity', 'remaining_quantity -' . $product_quantity . '', false);
        $this->db->set('move_quantity', 'move_quantity +' . $product_quantity . '', false);
        $this->db->where('product_id', $product_id);
        $this->db->update('products');


        $this->db->set('from_product_id',$product_id);
        $this->db->set('to_product_id',$to_id);
        $this->db->set('credit',$product_quantity);
        $this->db->set('type',"move");
        $this->db->set('transaction_by',$user_id);
        $this->db->set('created_at',date("Y-m-d H:i:s"));
        $this->db->insert('inventory_transactions');

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Product Moved Successfully!'
        );
        echo json_encode($result);
    }

    public function sale_item_details()
    {
        $product_id = $this->input->post('item_id');
        $purchaser_name = $this->input->post('purchaser_name');
        $purchaser_contact = $this->input->post('purchaser_contact');
        $sale_amount = $this->input->post('sale_amount');
        $sale_qty = $this->input->post('sale_qty');
        $last_edit = $this->input->post('user_id');
        $user = $this->db->get_where('users',array('user_id' => $last_edit))->row();

        $this->db->set('product_id',$product_id);
        $this->db->set('quantity',$sale_qty);
        $this->db->set('purchaser_name',$purchaser_name);
        $this->db->set('purchaser_contact',$purchaser_contact);
        $this->db->set('sale_amount',$sale_amount);
        $this->db->set('sold_by',$last_edit);
        $this->db->insert("asset_sales");
        $this->db->insert_id();

        $this->db->set('remaining_quantity', 'remaining_quantity -' . $sale_qty . '', false);
        $this->db->set('sale_quantity', 'sale_quantity +' . $sale_qty . '', false);
        $this->db->where('product_id', $product_id);
        $this->db->update('products');

//        $this->db->set('from_product_id',$product_id);
//        $this->db->set('credit',$sale_qty);
//        $this->db->set('type',"sale");
//        $this->db->set('transaction_by',$last_edit);
//        $this->db->set('created_at',date("Y-m-d H:i:s"));
//        $this->db->insert('inventory_transactions');

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Product Moved Successfully!'
        );
        echo json_encode($result);
    }

    public function get_sale_requests()
    {
        $from_date = $this->input->post("from_date");
        $to_date = $this->input->post("to_date");
        $user_id = $this->input->post("user_id");

        $this->db->select("asset_sales.*,product_names.product_name,rooms.room_name,campuses.campus_name,
        (select concat(first_name,' ',last_name) as created_by from users where users.user_id = asset_sales.sold_by) as created_user")
            ->join("products","products.product_id = asset_sales.product_id")
            ->join("product_names","product_names.product_name_id = products.product_name_id")
            ->join("campuses","campuses.campus_id = products.campus_id")
            ->join("rooms","rooms.room_id = products.room_id")
            ->order_by("asset_sales.created_at","DESC");
        $this->db->where("(sold_by = '$user_id')");
        $requests = $this->db->get_where('asset_sales',array('asset_sales.created_at >='=>$from_date." 00:00:00",'asset_sales.created_at <='=>$to_date." 23:59:59"))
            ->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'sale_response'=>$requests
        );
        echo json_encode($result);
    }

    public function update_sale_approval()
    {
        $status     = $this->input->post('status');
        $user_id    = $this->input->post('user_id');
        $id         = $this->input->post('id');

        $exp_request=$this->db->get_where('asset_sales','id = "'.$id.'"')->row();
        $this->db->set('status', $status);
        $this->db->set('approved_by', $user_id);
        $this->db->set('approved_date', date("Y-m-d H:i:s"));
        $this->db->where('id', $id);

        $ins = $this->db->update('asset_sales');
        if ($status == 'rejected') {
            $this->db->set('remaining_quantity', 'remaining_quantity +' . $exp_request->quantity . '', false);
            $this->db->set('sale_quantity', 'sale_quantity -' . $exp_request->quantity . '', false);
            $this->db->where('product_id', $exp_request->product_id);
            $this->db->update('products');
        }
        if ($ins) {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '1',
                'message' => 'Expense updated Successfully!'
            );
            echo json_encode($result);
        }
        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function delete_sale_request()
    {

        $id         = $this->input->post('id');
        $exp_request=$this->db->get_where('asset_sales','id = "'.$id.'"')->row();

        $this->db->set('remaining_quantity', 'remaining_quantity +' . $exp_request->quantity . '', false);
        $this->db->set('sale_quantity', 'sale_quantity -' . $exp_request->quantity . '', false);
        $this->db->where('product_id', $exp_request->product_id);
        $this->db->update('products');

        $this->db->where('id',$id);
        $ins = $this->db->delete('asset_sales');

        if ($ins) {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '1',
                'message' => 'Expense updated Successfully!'
            );
            echo json_encode($result);
        }
        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function post_sale()
    {
        $status     = "sold";
        $id         = $this->input->post('id');

        $exp_request=$this->db->get_where('asset_sales','id = "'.$id.'"')->row();
        $this->db->set('status', $status);
        $this->db->set('sold_date', date("Y-m-d H:i:s"));
        $this->db->where('id', $id);
        $ins = $this->db->update('asset_sales');

        $this->db->set('from_product_id',$exp_request->product_id);
        $this->db->set('credit',$exp_request->quantity);
        $this->db->set('type',"sale");
        $this->db->set('transaction_by',$exp_request->sold_by);
        $this->db->set('created_at',date("Y-m-d H:i:s"));
        $this->db->insert('inventory_transactions');
        if ($ins) {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '1',
                'message' => 'Expense updated Successfully!'
            );
            echo json_encode($result);
        }
        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function get_purchase_data_request()
    {
        $campuses = $this->db->select("*,0 as quantity,0 as selected_item,'' as product_error,'' as quantity_error")->get_where('product_names','has_sub = 0')->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'response_products'=>$campuses
        );
        echo json_encode($result);
    }

    public function insert_order()
    {
        $request_data = $this->input->post('request_data');
        $request_data = json_decode($request_data);

        $this->db->set('request_by',$this->input->post('user_id'));
        $this->db->insert('purchase_request');
        $insert_id = $this->db->insert_id();

        foreach($request_data as $req)
        {
            $this->db->set('request_id',$insert_id);
            $this->db->set('item_id',$req->selected_item);
            $this->db->set('quantity',$req->quantity);
            $this->db->set('inv_type','asset');
            $this->db->insert('po_request_products');
        }
        if ($insert_id) {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '1',
                'message' => 'Purchase Request added Successfully!'
            );
            echo json_encode($result);
        }
        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }

    }

    public function update_order()
    {
        $request_data = $this->input->post('request_data');
        $request_data = json_decode($request_data);
        $insert_id = $this->input->post('purchase_id');

        $this->db->where('request_id', $insert_id);
        $this->db->delete('po_request_products');

        foreach($request_data as $req)
        {
            $this->db->set('request_id',$insert_id);
            $this->db->set('item_id',$req->selected_item);
            $this->db->set('quantity',$req->quantity);
            $this->db->set('inv_type','asset');
            $this->db->insert('po_request_products');
        }
        if ($insert_id) {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '1',
                'message' => 'Purchase Request added Successfully!'
            );
            echo json_encode($result);
        }
        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }

    }

    public function get_purchase_requests()
    {
        $from_date = $this->input->post("from_date");
        $to_date = $this->input->post("to_date");
        $user_id = $this->input->post("user_id");

        $this->db->select("purchase_request.*,concat('PR-',purchase_request.id) as po_no,'' as po_products,
        ,(select concat(first_name,' ',last_name) as request_by from users where users.user_id = purchase_request.request_by) as created_user
        ,(select concat(first_name,' ',last_name) as approved_by from users where users.user_id = purchase_request.approved_by) as approved_user
        ")->order_by("purchase_request.created_at","DESC");
        $this->db->where("(purchase_request.request_by = '$user_id')");
        $requests = $this->db->get_where('purchase_request',array('purchase_request.created_at >='=>$from_date." 00:00:00",'purchase_request.created_at <='=>$to_date." 23:59:59"))
            ->result_array();

        foreach ($requests as $key=>$request){
            $data = $this->db->select('po_request_products.*,product_names.product_name')->join("product_names","product_name_id = po_request_products.item_id")->get_where("po_request_products","request_id = '".$request['id']."'")->result_array();
            $requests[$key]["po_products"] = json_encode($data);
        }

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'purchase_request_response'=>$requests
        );
        echo json_encode($result);
    }

    public function update_purchase_approval()
    {
        $status     = $this->input->post('status');
        $user_id    = $this->input->post('user_id');
        $id         = $this->input->post('id');

        $purch_request=$this->db->get_where('purchase_request','id = "'.$id.'"')->row();
        $carrier = $this->db->get_where("users", "user_id = '$purch_request->request_by'")->row();

        $this->db->set('status', $status);
        $this->db->set('approved_by', $user_id);
        $this->db->set('approved_date', date("Y-m-d H:i:s"));
        $this->db->where('id', $id);
        $ins = $this->db->update('purchase_request');
        if ($ins) {
            if ($carrier->device_id != null && $carrier->device_id != "" && $carrier->status == "1")
                $this->sendGCM("New Purchase Request has been $status", $carrier->device_id, "New Purchase Request $status");
            $result = array(
                'status' => 'FOUND',
                'response_code' => '1',
                'message' => 'Expense updated Successfully!'
            );
            echo json_encode($result);
        }
        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }

    }

    public function GetVendor(){

        // $staff_id = $this->input->post('staff_id');
        // $designation_id = $this->input->post('designation_id');

        // if($designation_id==24){
        $this->db->select(' * ');
        $this->db->from('vendor');
        $this->db->where(array('status'=>1));
        $staff = $this->db->get()->result_array();
        //     } else {
        // 		$this->db->select(' * ');
        // 		$this->db->from('chats');
        // 		$this->db->where(array('assign_id'=>$staff_id));
        // 		$chats = $this->db->get()->result_array();
        //     }

        if(count($staff)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'staff_response'=>$staff
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'staff_response'=>$staff
            );
            echo json_encode($result);


        }

    }

    public function insert_quotation()
    {
        $purchase_user_id = $this->input->post('user_id');
        $vendor_id = $this->input->post('vendor_id');
//        $description = $this->input->post('description');
        $bill_amount = $this->input->post('amount');
        $request_data = $this->input->post('request_data');
        $request_data = json_decode($request_data);

//        $category_id = $this->input->post('category_id');
//        $quantity = $this->input->post('quantity');
//        $unit_price = $this->input->post('unit_price');
//        $total_price = $this->input->post('total_price');
//        $inv_type = $this->input->post('inv_type');

        $this->db->set('description',"");
        $this->db->set('purchase_request_id',$this->input->post('request_id'));
        $this->db->set('vendor_id',$vendor_id);
        $this->db->set('total_amount',$bill_amount);
        $this->db->set('purchaser_id',$purchase_user_id);
        $this->db->set('created_by',$purchase_user_id);
        $this->db->insert('quotations');
        $insert_id = $this->db->insert_id();

        foreach($request_data as $req)
        {
            $this->db->set('quotation_id',$insert_id);
            $this->db->set('item_id',$req->product_name_id);
            $this->db->set('quantity',$req->quantity);
            $this->db->set('per_item_price',$req->per_unit);
            $this->db->set('total_price',$req->total_amount);
            $this->db->set('inv_type','asset');
            $this->db->insert('quotation_products');
        }

        if ($insert_id) {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '1',
                'message' => 'Purchase Request added Successfully!'
            );
            echo json_encode($result);
        }
        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function get_quotations()
    {
        $request_id = $this->input->post("request_id");

        $this->db->select("quotations.*,concat('QO-',quotations.id) as qo_no,'' as quotation_products,
        ,(select concat(first_name,' ',last_name) as request_by from users where users.user_id = quotations.created_by) as created_user
        ,(select concat(name,' ',phone) as vendor from vendors where vendors.id = quotations.vendor_id) as vendor
        ")->order_by("quotations.created_at","DESC");
        $requests = $this->db->get_where('quotations',"quotations.purchase_request_id = '$request_id'")->result_array();

        foreach ($requests as $key=>$request){
            $data = $this->db->select('quotation_products.*,product_names.product_name')->join("product_names","product_name_id = quotation_products.item_id")->get_where("quotation_products","quotation_id = '".$request['id']."'")->result_array();
            $requests[$key]["quotation_products"] = json_encode($data);
        }

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'quotation_response'=>$requests
        );
        echo json_encode($result);
    }

    public function approve_quotation()
    {
        $quotation = $this->db->get_where('quotations', 'quotations.id = "'.$this->input->post('quotation_id').'"')->row();
        $this->db->set('assigned_quotaion_id', $this->input->post('quotation_id'));
        $this->db->set('quotation_assign_date', date("Y-m-d h:i:s"));
        $this->db->where('id', $quotation->purchase_request_id);
        $this->db->update('purchase_request');

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Quotation Assigned Successfully!'
        );
        echo json_encode($result);
    }

    public function insert_po_order()
    {
        $quotation = $this->input->post('quotation_id');
        $user_id = $this->input->post('user_id');
        $quotation = $this->db->get_where("quotations","id = $quotation")->row();
        $vendor = $this->db->get_where("vendors","id = $quotation->vendor_id")->row();
        $purchase_user_id = $quotation->purchaser_id;
        $vendor_name = $vendor->name;
        $description = "";
        $vendor_address = $vendor->address;
        $bill_amount = $quotation->total_amount;


        $this->db->set('purchase_request_id',$quotation->purchase_request_id);
        $this->db->set('quotation_id',$quotation->id);
        $this->db->set('description',$description);
        $this->db->set('vendor_id',$vendor->id);
        $this->db->set('vendor_name',$vendor_name);
        $this->db->set('vendor_address',$vendor_address);
        $this->db->set('total_amount',$bill_amount);
        $this->db->set('purchaser',$purchase_user_id);
        $this->db->set('created_by',$user_id);
        $this->db->set('status','0');
        $this->db->insert('purchase_order');
        $insert_id = $this->db->insert_id();

        $request_data = $this->db->get_where("quotation_products","quotation_id = $quotation->id")->result_array();
        foreach($request_data as $req)
        {
            $this->db->set('po_id',$insert_id);
            $this->db->set('item_id',$req['item_id']);
            $this->db->set('quantity',$req['quantity']);
            $this->db->set('per_item_price',$req['per_item_price']);
            $this->db->set('total_price',$req['total_price']);
            $this->db->set('inv_type',"asset");
            $this->db->insert('po_products');
        }

        if ($insert_id) {

            $this->db->set('purchase_order_id', $insert_id);
            $this->db->set('quotation_assign_date', date("Y-m-d h:i:s"));
            $this->db->where('id', $quotation->purchase_request_id);
            $this->db->update('purchase_request');

            $result = array(
                'status' => 'FOUND',
                'response_code' => '1',
                'message' => 'Purchase Order added Successfully!'
            );
            echo json_encode($result);
        }
        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }

    }

    public function get_all_purchase_orders()
    {
        $user_id = $this->input->post("user_id");
        $from_date = $this->input->post("from_date");
        $to_date = $this->input->post("to_date");

        $this->db->select("purchase_order.*,concat('PO-',purchase_order.id) as qo_no,'' as po_products
        ,(select concat(first_name,' ',last_name) as request_by from users where users.user_id = purchase_order.created_by) as purchaser")
            ->order_by("purchase_order.created_at","DESC");
        $this->db->where(array('purchase_order.created_at >='=>$from_date." 00:00:00",'purchase_order.created_at <='=>$to_date." 23:59:59"));
        $requests = $this->db->get_where('purchase_order',"purchase_order.purchaser = '$user_id'")->result_array();

        foreach ($requests as $key=>$request){
            $data = $this->db->select('po_products.*,product_names.product_name')->join("product_names","product_name_id = po_products.item_id")->get_where("po_products","po_id = '".$request['id']."'")->result_array();
            $requests[$key]["po_products"] = json_encode($data);
        }

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'purchase_order_response'=>$requests
        );
        echo json_encode($result);
    }

    public function get_po_expenses()
    {
        $po_id = $this->input->post("po_id");

        $requests = $this->db->select("expenses.*,expense_category.name as expense_name")->join("expense_category","expense_category.expense_category_id = expenses.expense_category_id")->get_where('expenses',"expenses.po_no = '$po_id'")->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'purchase_expenses_response'=>$requests
        );
        echo json_encode($result);
    }

    public function post_po_expense()
    {

        $amount_post = $this->input->post('amount');
        $po_id = $this->input->post('po_id');
        $user_id = $this->input->post('user_id');
        $description = $this->input->post('description');
        $cash = $this->db->get_where("petty_cash_college_wise","assign_to = '$user_id' and petty_status = '1'")->result_array();

        if (count($cash) > 0)
        {
            $amount = pettycash_statement($cash[0]['id']);
            if ($amount > $amount_post) {

                //load the helper
                $this->load->helper('form');

                //Configure
                //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
                $config['upload_path'] = 'uploads/';

                // set the filter image types
                $config['allowed_types'] = 'gif|jpg|png';

                //load the upload library
                $this->load->library('upload', $config);

                $this->upload->initialize($config);

                $this->upload->set_allowed_types('*');

                $data['upload_data'] = '';

                //if not successful, set the error message
                if (!$this->upload->do_upload('image')) {
                    $image = '';
                }
                else {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if ($data['upload_data']['file_name']) {
                        $image = $data['upload_data']['file_name'];
                    }
                }

                $user = $this->db->get_where('users',"user_id = '".$user_id."'")->row();
                $this->db->set('campus_id',$user->campus_id);
                $this->db->set('expense_category_id',"108");
                $this->db->set('title',"Purchase against PO-".$po_id);
                $this->db->set('date',date('Y-m-d'));
                $this->db->set('amount',$amount_post);
                $this->db->set('purpose',$description);
                $this->db->set('actual_date', date('Y-m-d H:i:s'));
                $this->db->set('image', $image);
                $this->db->set('approved_status', '1');
                $this->db->set('po_no', $po_id);
                $this->db->set('add_by_id', $user_id);
                $this->db->set('add_by', $user->first_name." ".$user->last_name);
                $this->db->insert('expenses');

                $this->db->set('paid_amount', 'paid_amount +' . $amount_post . '', false);
                $this->db->set('status', "purchased");
                $this->db->where('id', $po_id);
                $this->db->update('purchase_order');

                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '1',
                    'message' => "Expenses Posted Successfully"
                );
                echo json_encode($result);
            }
            else {
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '0',
                    'message' => "Your PettyCash is $amount and Your Expense Amount is $amount_post"
                );
                echo json_encode($result);
            }
        }

        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'You have no PettyCash'
            );
            echo json_encode($result);
        }
    }

    public function get_all_pending_po()
    {

        $this->db->select("purchase_order.*,concat('PO-',purchase_order.id) as qo_no,'' as po_products
        ,(select concat(first_name,' ',last_name) as request_by from users where users.user_id = purchase_order.created_by) as purchaser")
            ->order_by("purchase_order.created_at","DESC");

        $requests = $this->db->get_where('purchase_order',"purchase_order.status = 'purchased'")->result_array();

        foreach ($requests as $key=>$request){
            $data = $this->db->select('po_products.*,product_names.product_name')->join("product_names","product_name_id = po_products.item_id")->get_where("po_products","po_id = '".$request['id']."'")->result_array();
            $requests[$key]["po_products"] = json_encode($data);
        }

        $campuses = $this->db->get_where("campuses","status = '1'")->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'purchase_order_response'=>$requests,
            'response_campuses'=>$campuses
        );
        echo json_encode($result);
    }

    public function insert_grn()
    {
        $purchase_user_id = $this->input->post('user_id');
        $po_id = $this->input->post('po_id');
        $request_data = $this->input->post('request_data');
        $campus_id = $this->input->post('campus_id');
        $request_data = json_decode($request_data);

        $user = $this->db->get_where("users","user_id = '$purchase_user_id'")->row();

        $this->db->set('purchase_order_no',$po_id);
        $this->db->set('created_by',$purchase_user_id);
        $this->db->set('campus_id',$campus_id);
        $this->db->insert('grn');
        $insert_grn = $this->db->insert_id();

        foreach($request_data as $req)
        {
            $this->db->set('po_id',$po_id);
            $this->db->set('grn_id',$insert_grn);
            $this->db->set('product_id',$req->product_name_id);
            $this->db->set('qty',$req->received_qty);
            $this->db->set('price_per',$req->per_unit);
            $this->db->insert('grn_products');

            $this->db->set('received_qty', 'received_qty +' . $req->received_qty . '', false);
            $this->db->where('id', $req->id);
            $this->db->update('po_products');

            for ($i = 1;$i<=$req->received_qty;$i++){
                $product_id = $this->insert_to_inventory($campus_id,$po_id,$req->product_name_id,1,"",$purchase_user_id,$user->first_name." ".$user->last_name);
                $this->db->set('to_product_id',$product_id);
                $this->db->set('debit',1);
                $this->db->set('po_no',$po_id);
                $this->db->set('grn_no',$insert_grn);
                $this->db->set('type',"purchased");
                $this->db->set('transaction_by',$purchase_user_id);
                $this->db->set('created_at',date("Y-m-d H:i:s"));
                $this->db->insert('inventory_transactions');
            }
        }

        if ($insert_grn) {
            $last_po = $this->db->select('sum(quantity) as qty,sum(received_qty) as receive_qty')->get_where("po_products","po_id = '$po_id'")->row();
            if ($last_po->qty == $last_po->receive_qty){
                $this->db->set("status","completed");
                $this->db->where("id",$po_id);
                $this->db->update("purchase_order");
            }
            $result = array(
                'status' => 'FOUND',
                'response_code' => '1',
                'message' => 'Purchase Request added Successfully!'
            );
            echo json_encode($result);
        }
        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function insert_to_inventory($campus_id,$po,$product,$qty,$purchase_slip,$user_id,$user_name)
    {
        $room = $this->db->get_where('rooms',"room_name = 'Main Inventory' and campus_id = '$campus_id'")->row()->room_id;
        $this->db->set('campus_id',$campus_id);
        $this->db->set('room_id',$room);
        $this->db->set('subroom_id','');
        $this->db->set('product_name_id',$product);
        $this->db->set('purchase_slip',$purchase_slip);
        $this->db->set('product_quantity',$qty);
        $this->db->set('remaining_quantity',$qty);
        $this->db->set('product_guarantee',"0");
        $this->db->set('product_guarantee_start_date',"0000-00-00");
        $this->db->set('product_guarantee_end_date',"0000-00-00");
        $this->db->set('remarks',"Received in Stock of PO-$po");
        $this->db->set('user_id',$user_id);
        $this->db->set('reponsilble_user_id',$user_id);
        $this->db->set('po_no',$po);
        $this->db->set('status',"1");
        $this->db->set('add_by',$user_name);
        $this->db->set('last_edit',$user_name);
        $this->db->set('clear_by',$user_name);
        $this->db->insert('products');
        return $this->db->insert_id();
    }

    public function get_all_grns()
    {
        $user_id = $this->input->post("user_id");
        $from_date = $this->input->post("from_date");
        $to_date = $this->input->post("to_date");

        $this->db->select("grn.*,concat('GRN-',grn.id) as qo_no,'' as grn_products,(select concat(first_name,' ',last_name) as created_user from users where users.user_id = grn.created_by) as created_user,campuses.campus_name")
            ->join("campuses","campuses.campus_id = grn.campus_id")
            ->order_by("grn.created_at","DESC");
        $this->db->where(array('grn.created_at >='=>$from_date." 00:00:00",'grn.created_at <='=>$to_date." 23:59:59"));
        $requests = $this->db->get('grn')->result_array();

        foreach ($requests as $key=>$request){
            $data = $this->db->select('grn_products.*,product_names.product_name')->join("product_names","product_name_id = grn_products.product_id")->get_where("grn_products","grn_id = '".$request['id']."'")->result_array();
            $requests[$key]["grn_products"] = json_encode($data);
        }

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'grn_response'=>$requests
        );
        echo json_encode($result);
    }

    public function ledger_details()
    {
        $types = array();
        $type_cash = array("type"=>"cash","amount"=>"0","campuses"=>array());
        $type_bank = array("type"=>"bank","amount"=>"0","accounts"=>array());

        $campuses = $this->db->select("campuses.campus_id,campuses.campus_name,0 as total_recovery,'' as courses")->join("campuses","campuses.campus_id = closing_persons.campus_id")->get_where("closing_persons",'closing_persons.active_status = 1')->result_array();

        $accounts = $this->db->query('SELECT *,"0" as total_recovery,"" as campuses FROM `accounts` WHERE `type` = "1"')->result_array();
        foreach ($accounts as $key=>$account){
            $accounts[$key]['campuses'] = $campuses;
            foreach($campuses as $i=>$campus){
                $campus_id = $campus['campus_id'];
                $accounts[$key]['campuses'][$i]['courses'] =  $this->db->select("course_id,course_name,'0' as total_recovery")->where("FIND_IN_SET('$campus_id',campus_ids) !=", 0)->get("courses")->result_array();
            }
        }

        $from_date=$this->input->post('from_date');
        $to_date=$this->input->post('to_date');
        $campuses_data = array();
        foreach ($campuses as $a=>$campus) {

            $campus_id = $campus['campus_id'];
            $courses = $this->db->select("course_id,course_name,'0' as total_recovery")->where("FIND_IN_SET('$campus_id',campus_ids) !=", 0)->get("courses")->result_array();
            $campus_courses = $courses;

            $this->db->select('sum(asset_sales.sale_amount) as total');
            $this->db->from('asset_sales');
            $this->db->join('products', 'products.product_id = asset_sales.product_id', 'inner');
            $this->db->where("asset_sales.sold_date >= '$from_date 00:00:00' and asset_sales.sold_date <= '$to_date 23:59:59' and products.campus_id = '" . $campus['campus_id'] . "'");
            $asset_sales_sum_today = $this->db->get()->result_array();

            $this->db->select('actual_amount,fee_pay_through,bank_reconciliation_statement.account_id,students.course_id,campuses.campus_id as student_campus_id');
            $this->db->from('payments');
            $this->db->join('bank_reconciliation_statement', 'bank_reconciliation_statement.id = payments.statement_id', 'left');
            $this->db->join('students', 'students.student_id = payments.student_id', 'left');
            $this->db->join('courses', 'courses.course_id = payments.course_id', 'left');
            $this->db->join('classes', 'classes.class_id = students.class_id', 'left');
            $this->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'left');
            $this->db->where('submitted_fee_campus_id', $campus['campus_id']);
            $this->db->where('paid_date >= "'.$from_date.'" and paid_date <= "'.$to_date.'"  and payments.paid = 1');
            $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan ELSE challan_no END",false);
            $query = $this->db->get()->result_array();

            $total_amount_bank = 0;
            $total_amount_cash = 0;
            foreach ($query as $amount){
                if ($amount['fee_pay_through']=='college') {
                    $total_amount_cash = $total_amount_cash + $amount['actual_amount'];
                    $find_index = $this->array_search_by_key($courses,"course_id",$amount['course_id']);
                    $courses[$find_index]['total_recovery']+=$amount['actual_amount'];
                }
                else {
                    $total_amount_bank = $total_amount_bank + $amount['actual_amount'];
                    if ($amount['account_id'] != ""){
                        $find_index = $this->array_search_by_key($accounts,"id",$amount['account_id']);
                        $accounts[$find_index]['total_recovery']+=$amount['actual_amount'];
                        $account_campus_index = $this->array_search_by_key($accounts[$find_index]['campuses'],"campus_id",$amount['student_campus_id']);
                        if ($account_campus_index == null) {
                            foreach($accounts[$find_index]['campuses'] as $data=>$element) {
                                if($element["campus_id"] == $amount['student_campus_id']) {
                                    $account_campus_index = $data;
                                }
                            }

                        }
                        $accounts[$find_index]['campuses'][$account_campus_index]['total_recovery'] += $amount['actual_amount'];
                        $campus_course_index = $this->array_search_by_key($accounts[$find_index]['campuses'][$account_campus_index]['courses'],"course_id",$amount['course_id']);
                        $accounts[$find_index]['campuses'][$account_campus_index]['courses'][$campus_course_index]['total_recovery']+=$amount['actual_amount'];
                    }
                    $campus_course_index = $this->array_search_by_key($campus_courses,"course_id",$amount['course_id']);
                    $campus_courses[$campus_course_index]['total_recovery']+=$amount['actual_amount'];
                }
            }
            array_push($campuses_data,array("campus_id"=>$campus['campus_id'],"campus_name"=>$campus['campus_name'],"amount"=>$total_amount_cash+$asset_sales_sum_today[0]['total'],"courses"=>$courses));
            $type_cash['amount'] = $type_cash['amount']+$total_amount_cash+$asset_sales_sum_today[0]['total'];
            $type_bank['amount'] = $type_bank['amount']+$total_amount_bank;
        }
        $type_cash['campuses'] = $campuses_data;
        $type_bank['accounts'] = $accounts;
        array_push($types,$type_cash);
        array_push($types,$type_bank);
        $main_details = array("type" => "Total","amount"=>$type_cash['amount']+$type_bank['amount'],"details"=>$types);
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'recovery_response'=>$main_details
        );
        echo json_encode($result);
    }

    function array_search_by_key($array, $key, $value) {

        foreach($array as $data=>$element) {
            if($element[$key] == $value) {
                return $data;
            }
        }
        return null;
    }

    public function all_closings()
    {
        $data['campuses'] = $this->account->getCampuses();

        if ($this->input->post('to_date') == '')
            $today = date('Y-m-d');
        else
            $today=$this->input->post('to_date');

        $this->db->select('campuses.campus_name,concat(users.first_name," ",users.last_name) as closer,closing_persons.campus_id as campus_id');
        $this->db->from('closing_persons');
        $this->db->join('campuses','campuses.campus_id = closing_persons.campus_id','left');
        $this->db->join('users','users.user_id = closing_persons.user_id','left');
//        if(@$this->session->userdata('role') != 'Admin'){
//            $this->db->where('closing_persons.user_id = "'.$this->session->userdata('user_id').'" and active_status = 1');
//        }
//        else
//        {
        $this->db->where('closing_persons.active_status = "1"');
//        }
        $dataclose = $this->db->get()->result_array();

        $sq = 'select closing_perday.campus_id,campus_name,(select for_day from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
		(select for_month from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,MAX(for_year) as year from closing_perday left join campuses on campuses.campus_id = closing_perday.campus_id GROUP by closing_perday.campus_id';
        $data['campusclosings'] = $this->db->query($sq)->result_array();

//        $sq = 'select closing_perday.campus_id,campus_name,
//		(select for_day from closing_perday where campus_id = campuses.campus_id and checked_by = "1"
//		order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
//		(select for_month from closing_perday where campus_id = campuses.campus_id and checked_by = "1"
//		order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,MAX(for_year) as year from closing_perday left join campuses on campuses.campus_id = closing_perday.campus_id GROUP by closing_perday.campus_id';
//        $data['campusclosingverified'] = $this->db->query($sq)->result_array();




//        if(@$this->session->userdata('role') != 'Admin'){
//            foreach($data['campusclosings'] as $key=>$cam)
//            {
//                if($cam['campus_id'] != $this->session->userdata('user_campus_id'))
//                {
//                    unset($data['campusclosings'][$key]);
//                }
//            }
//        }

//        $data['campusclosings'] = array_values($data['campusclosings']);

        foreach ($dataclose as $key=>$closing)
        {

            $sq = "select * from closing_perday where campus_id = '".$closing['campus_id']."' and for_month = '".date('m', strtotime($today))."' and for_day = '".date('d', strtotime($today))."'and for_year = '".date('Y', strtotime($today))."'";
            $closed = $this->db->query($sq)->result_array();

            if (count($closed)>0) {
                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('closing_id = "'.$closed[0]['campus_closing_id'].'"');
                $query = $this->db->get()->result_array();
                $value = array_sum(array_column($query,'actual_amount'));
                $dataclose[$key]['closing_amount'] = $value;
                $dataclose[$key]['closed_status'] = '1';
                $dataclose[$key]['closing_id'] = $closed[0]['id'];
                $dataclose[$key]['transaction_no'] = $closed[0]['transaction_no'];
                $dataclose[$key]['close_type'] = $closed[0]['close_type'];
                $dataclose[$key]['closed_by'] = $closed[0]['closed_by'];
                $dataclose[$key]['checked_by'] = $closed[0]['checked_by'];
                $dataclose[$key]['account_id'] = $closed[0]['account_id'];
            }
            else {
                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                $this->db->where('fee_pay_through = "college"');
                $this->db->where('actual_paid_date = "'.$today.'"');
                $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
                $query = $this->db->get()->result_array();

                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                $this->db->where('merged_challan is null');
                $this->db->where('fee_pay_through = "college"');
                $this->db->where('actual_paid_date = "'.$today.'"');
                $this->db->where('payments.paid = 1');
                $query2 = $this->db->get()->result_array();

                $yesterday = date('Y-m-d', strtotime($today. ' - 1 days'));

                $sq = "select * from closing_perday where campus_id = '".$closing['campus_id']."' and for_month = '".date('m', strtotime($yesterday))."' and for_day = '".date('d', strtotime($yesterday))."'and for_year = '".date('Y', strtotime($yesterday))."'";
                $closed = $this->db->query($sq)->result_array();

                if (count($closed)>0)  {

                    $this->db->select('*');
                    $this->db->from('payments');
                    $this->db->join('students','students.student_id = payments.student_id','left');
                    $this->db->join('courses','courses.course_id=students.course_id','inner');
                    $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                    $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                    $this->db->where('fee_pay_through = "college"');
                    $this->db->where('actual_paid_date = "'.$yesterday.'"');
                    $this->db->where('closing_id IS NULL');
                    $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
                    $query3 = $this->db->get()->result_array();

                    $this->db->select('*');
                    $this->db->from('payments');
                    $this->db->join('students','students.student_id = payments.student_id','left');
                    $this->db->join('courses','courses.course_id=students.course_id','inner');
                    $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                    $this->db->where('merged_challan is null');
                    $this->db->where('fee_pay_through = "college"');
                    $this->db->where('actual_paid_date = "'.$yesterday.'"');
                    $this->db->where('closing_id IS NULL');
                    $this->db->where('payments.paid = 1');
                    $query4 = $this->db->get()->result_array();
                    $final = array_merge($query3, $query4,$query, $query2);
                }

                else {
                    $final = array_merge($query, $query2);
                }

                $value = array_sum(array_column($final,'actual_amount'));

                $this->db->select('sum(asset_sales.sale_amount) as total');
                $this->db->from('asset_sales');
                $this->db->join('products','products.product_id = asset_sales.product_id','inner');
                $this->db->where("asset_sales.sold_date >= '$today 00:00:00' and asset_sales.sold_date <= '$today 23:59:59' and products.campus_id = '".$closing['campus_id']."'");
                $asset_sales_sum_today = $this->db->get()->result_array();

                $this->db->select('sum(asset_sales.sale_amount) as total');
                $this->db->from('asset_sales');
                $this->db->join('products','products.product_id = asset_sales.product_id','inner');
                $this->db->where("asset_sales.sold_date >= '$yesterday 00:00:00' and asset_sales.sold_date <= '$yesterday 23:59:59' and products.campus_id = '".$closing['campus_id']."' and closing_id IS NULL");
                $asset_sales_sum_yesterday = $this->db->get()->result_array();

                $asset_sale_amount = $asset_sales_sum_today[0]['total'] + $asset_sales_sum_yesterday[0]['total'];

                $this->db->select('sum(sales_payments.payment_amount) as total');
                $this->db->from('sales');
                $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
                $this->db->join('people','people.person_id  = sales.customer_id','inner');
                $this->db->where("sales.sale_time >= '$today 00:00:00' and sales.sale_time <= '$today 23:59:59' and sales.campus_id = '".$closing['campus_id']."'");
                $sales_sum = $this->db->get()->result_array();

                $this->db->select('sum(sales_payments.payment_amount) as total');
                $this->db->from('sales');
                $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
                $this->db->join('people','people.person_id  = sales.customer_id','inner');
                $this->db->where("sales.sale_time >= '$yesterday 00:00:00' and sales.sale_time <= '$yesterday 23:59:59' and sales.campus_id = '".$closing['campus_id']."'");
                $sales_sum_yesterday = $this->db->get()->result_array();

                $sale_amount = $sales_sum[0]['total'] + $sales_sum_yesterday[0]['total'];

                $dataclose[$key]['closing_amount'] = $value+$sale_amount+$asset_sale_amount;
                $dataclose[$key]['closed_status'] = '0';
                $dataclose[$key]['closing_id'] = '';
                $dataclose[$key]['close_type'] = '0';
                $dataclose[$key]['transaction_no'] = '';
                $dataclose[$key]['closed_by'] = '';
                $dataclose[$key]['checked_by'] = '';
                $dataclose[$key]['account_id'] = '';
            }
        }

        $data['closings']=$dataclose;
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'recovery_response'=>$data['closings']
        );
        echo json_encode($result);
    }

    public function Typed_recovery_data()
    {
        $data = $this->input->post("type_data");
        $data = json_decode($data);

        $this->db->select('CONCAT(students.first_name," ",students.last_name) as name,students.roll_no,actual_amount,fee_pay_through,bank_reconciliation_statement.account_id,students.course_id,payments.paid_date,courses.course_name,campuses.campus_name as student_campus');
        $this->db->from('payments');
        $this->db->join('bank_reconciliation_statement', 'bank_reconciliation_statement.id = payments.statement_id', 'left');
        $this->db->join('students', 'students.student_id = payments.student_id', 'left');
        $this->db->join('courses', 'courses.course_id = payments.course_id', 'left');
        $this->db->join('classes', 'classes.class_id = students.class_id', 'left');
        $this->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'left');
        if ($data->campus_id != null && $data->campus_id != "" && $data->account_id == "")
            $this->db->where('payments.submitted_fee_campus_id', $data->campus_id);
        if ($data->course_id != null && $data->course_id != "")
            $this->db->where('payments.course_id', $data->course_id);
        if ($data->account_id != null && $data->account_id != "")
            $this->db->where('bank_reconciliation_statement.account_id', $data->account_id);
        else
            $this->db->where('bank_reconciliation_statement.account_id is NULL');
        if ($data->campus_id != "" && $data->account_id != "")
            $this->db->where('classes.campus_id', $data->campus_id);

        $this->db->where('paid_date >= "'.$data->start_date.'" and paid_date <= "'.$data->end_date.'"  and payments.paid = 1');
        $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan ELSE challan_no END",false);
        $query = $this->db->get()->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'payments_response'=>$query
        );
        echo json_encode($result);
    }

    public function is_allowed()
    {

        $query = $this->db->get("test_ali")->row();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'ali_allowed'=>$query
        );
        echo json_encode($result);
    }

    public function insert_old_item() {

        $user_id            =$this->input->post('user_id');
        $campus_id          =$this->input->post('campus_id');
        $room_id            =$this->input->post('room_id');
        $subroom_id         =$this->input->post('subroom_id');
        $product_id         =$this->input->post('product_id');
        $product_quantity   =$this->input->post('qty');
        $reason             =$this->input->post('reason');
        $qr                 =$this->input->post('qr');
        $price                 =$this->input->post('price');

        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'inventory_images/';

        // set the filter image types
        $config['allowed_types'] = 'gif|jpg|png';

        //load the upload library
        $this->load->library('upload', $config);

        $this->upload->initialize($config);
        $this->upload->set_allowed_types('*');
        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('image')) {
            $image = '';
        }
        else {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if ($data['upload_data']['file_name']) {
                $image = $data['upload_data']['file_name'];
            }
        }

        $user = $this->db->get_where("users","user_id = '$user_id'")->row();
        $user_name = $user->first_name.' '.$user->last_name;
        $this->db->set('campus_id',$campus_id);
        $this->db->set('room_id',$room_id);
        $this->db->set('subroom_id',$subroom_id);
        $this->db->set('product_name_id',$product_id);
        $this->db->set('purchase_slip',$image);
        $this->db->set('product_quantity',$product_quantity);
        $this->db->set('remaining_quantity',$product_quantity);
        $this->db->set('product_guarantee',"0");
        $this->db->set('product_guarantee_start_date',"0000-00-00");
        $this->db->set('product_guarantee_end_date',"0000-00-00");
        $this->db->set('remarks',"OLD ITEM ".$reason);
        $this->db->set('user_id',$user_id);
        $this->db->set('reponsilble_user_id',$user_id);
        $this->db->set('qr_code',$qr);
        $this->db->set('po_no',0);
        $this->db->set('status',"1");
        $this->db->set('add_by',$user_name);
        $this->db->set('last_edit',$user_name);
        $this->db->set('clear_by',$user_name);
        $this->db->set('estimated_price',$price);
        $this->db->insert('products');
        $this->db->insert_id();

        $result = array(
            'status' => 'FOUND',
            'response_code' => '1',
            'message' => "Item Posted Successfully"
        );
        echo json_encode($result);
    }

    public function addstruckofdetails()
    {
        $image = '';
        $post_image = '';
        $whatsapp_image = '';
        $sms_image = '';
        $recording = '';

        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'struck_of_data/';

        // set the filter image types
        $config['allowed_types'] = 'gif|jpg|png';

        //load the upload library
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->set_allowed_types('*');
        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('image')) {
            $data = array('msg' => $this->upload->display_errors());
            $image = '';
        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = $data['upload_data']['file_name'];
            }
        }

        if (!$this->upload->do_upload('post_image')) {
            $data = array('msg' => $this->upload->display_errors());
            $post_image = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $post_image = $data['upload_data']['file_name'];
            }
        }

        if (!$this->upload->do_upload('whatsapp_image')) {
            $data = array('msg' => $this->upload->display_errors());
            $whatsapp_image = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $whatsapp_image = $data['upload_data']['file_name'];
            }
        }

        if (!$this->upload->do_upload('sms_image'))
        {
            $data = array('msg' => $this->upload->display_errors());
            $sms_image = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $sms_image = $data['upload_data']['file_name'];
            }
        }

        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'struck_of_data/';

        // set the filter image types
        $config['allowed_types'] = '*';

        //load the upload library
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->set_allowed_types('*');
        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('recording'))
        {
            $data = array('msg' => $this->upload->display_errors());
            $recording = '';
        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $recording = $data['upload_data']['file_name'];
            }
        }

        $studentid = $this->input->post('student_id');
        $this->db->set('student_id', $studentid);
        $this->db->set('contact_from_no', $this->input->post('fromno'));
        $this->db->set('contact_to_no', $this->input->post('tono'));
        $this->db->set('action_type', $this->input->post('delete_type'));
        $this->db->set('reason', '');
        $this->db->set('detail', '');
        $this->db->set('created_by',$this->input->post('user_id'));
        $this->db->set('status',"0");
        $this->db->set('proof_image',$image);
        $this->db->set('post_receipt',$post_image);
        $this->db->set('whatsapp_image',$whatsapp_image);
        $this->db->set('sms_image',$sms_image);
        $this->db->set('recording',$recording);
        $this->db->insert('struckofdetails_students');

        if($this->input->post('amount') != ''){
            $this->db->set('title','Expense For Struck of Letter Post');
            $this->db->set('date',date('Y-m-d'));
            $this->db->set('amount',$this->input->post('amount'));
            $this->db->set('purpose','Expense for Struck of Letter post to student');
            $this->db->set('actual_date', date('Y-m-d H:i:s'));
            $this->db->set('image', $post_image);
            $this->db->set('expense_category_id', '32');
            $this->db->set('add_by_id', $this->input->post('user_id'));
            $this->db->set('campus_id', $this->db->join('classes','classes.class_id = students.class_id')->get_where('students','student_id = "'.$studentid.'"')->row()->campus_id);
            $this->db->set('add_by', $this->input->post('name'));
            $this->db->insert('expenses');

            $this->db->set('remaining_amount', 'remaining_amount -' . $this->input->post('amount') . '', false);
            $this->db->where('assign_to', $this->session->userdata('user_id'));
            $this->db->update('petty_cash_college_wise');
        }
        $result = array(
            'status' => 'FOUND',
            'response_code' => '1',
            'message' => "Item Posted Successfully"
        );
        echo json_encode($result);
    }

    public function get_all_campus_expenses()
    {

        $to_date = $this->input->post("to_date");
        $requests = $this->db->select("*,'' as expenses,
        (select sum(amount) as total_exp from expenses where expenses.actual_date >= '$to_date 00:00:00' and expenses.actual_date <= '$to_date 23:59:59' and expenses.campus_id = campuses.campus_id) as total_sum")
            ->get('campuses')->result_array();

        foreach ($requests as $key=>$request){
            $cam = $request['campus_id'];
            $data = $this->db->select("*,
            expense_category.name as expense_name,
            expense_category.type as expense_type,
            (select concat(first_name,' ',last_name) as approved_user from users where users.user_id = expense_request.approval_first_by) as approved_user,
            (select concat(first_name,' ',last_name) as created_user from users where users.user_id = expense_request.created_by) as created_user,
            (select concat(first_name,' ',last_name) as second_approved_user from users where users.user_id = expense_request.approval_second_by) as second_approved_user")
                ->join('expense_category','expense_category.expense_category_id  = expenses.expense_category_id','left')
                ->join('expense_request','expense_request.id  = expenses.request_id','left')
                ->get_where("expenses","expenses.actual_date >= '$to_date 00:00:00' and expenses.actual_date <= '$to_date 23:59:59' and expenses.campus_id = '$cam'")
                ->result_array();
            $requests[$key]["expenses"] = $data;
        }

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'closing_date' => $this->db->select("*")->order_by("id","DESC")->get("expense_closings")->row(),
            'campus_expenses_response'=>$requests
        );
        echo json_encode($result);
    }

    public function post_verify_expense()
    {
        $user_id = $this->input->post('user_id');
        $date = $this->input->post('to_date');
        $amount = $this->input->post('amount');

        $this->db->set('total_amount',$amount);
        $this->db->set('date',$date);
        $this->db->set('created_by',$user_id);
        $this->db->insert('expense_closings');
        $insert_grn = $this->db->insert_id();

        if ($insert_grn) {

            $result = array(
                'status' => 'FOUND',
                'response_code' => '1',
                'message' => 'Purchase Request added Successfully!'
            );
            echo json_encode($result);
        }
        else {
            $result = array(
                'status' => 'FOUND',
                'response_code' => '0',
                'message' => 'Something Went Wrong Apply Again!'
            );
            echo json_encode($result);
        }
    }

    public function get_student_attendence()
    {
        $campuses = $this->db->get("campuses")->result_array();
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'response_campuses' => $campuses
        );
        echo json_encode($result);
    }

    public function search_student_attendence()
    {
        $select_date = $this->input->post('select_date');
        $campus_id = $this->input->post('campus_id');

        $this->db->select('CONCAT(students.first_name," ",students.last_name) as student_name,students.student_id,students.father_name,students.roll_no ,attendence.time,classes.session,campuses.campus_name as study_campus,0 as unpaid,courses.course_name');
        $this->db->from('attendence');
        $this->db->join('machine_data','machine_data.machine_id = attendence.machine_user_id','inner');
        $this->db->join('students','students.student_id=machine_data.teacher_student_id','inner');
        $this->db->join('classes','classes.class_id=students.class_id','inner');
        $this->db->join('campuses','campuses.campus_id=classes.campus_id','inner');
        $this->db->join('courses','courses.course_id=students.course_id','inner');
        $this->db->where(array('machine_data.type'=>'student','students.status'=>1,'attendence.campus_code' => $campus_id,
            'attendence.time >=' => $select_date.' 00:00:00','attendence.time <=' => $select_date.' 23:59:59'));
        $this->db->group_by(array("attendence.machine_user_id", "students.student_id"));
        $this->db->order_by("attendence.time","ASC");
        $attendences = $this->db->get()->result_array();
        foreach ($attendences as $key=>$attendence){
            $student_id = $attendence['student_id'];
            $attendences[$key]['unpaid'] = $this->db->select('count(*) as total')->get_where("payments","dead_line <= '$select_date' and paid = 0 and student_id = '$student_id'")->row()->total;
        }

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'response_attendence' => $attendences
        );
        echo json_encode($result);
    }

    public function classesStatus()
    {
        $session = $this->input->post("session");
        $sessions = $this->db->select("session")->from("classes")->where("status",1)->group_by("session")->get()->result_array();
        if ($session) {
            $sql = "SELECT MAX(classes.name) AS name, COUNT(students.class_id) AS total_students, classes.class_id, classes.seats
                        FROM classes
                        LEFT JOIN students ON students.class_id = classes.class_id
                        WHERE classes.status=1 AND students.status=1 AND classes.session = '$session'
                        GROUP BY classes.class_id
                        ORDER BY class_id";
            $query = $this->db->query($sql)->result_array();
        }else
            $query = array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'response_sessions' => $sessions,
            'response_session_details' => $query
        );
        echo json_encode($result);


    }

    public function insert_construction_site() {

        $user_id       =$this->input->post('user_id');
        $title         =$this->input->post('name');
        $description   =$this->input->post('description');


        $this->db->set('name',$title);
        $this->db->set('description',$description);
        $this->db->set('created_by',$user_id);
        $this->db->insert('construction_site');

        $result = array(
            'status' => 'FOUND',
            'response_code' => '1',
            'message' => "Item Posted Successfully"
        );
        echo json_encode($result);
    }

    public function update_construction_site() {

        $user_id       =$this->input->post('site_id');
        $title         =$this->input->post('name');
        $description   =$this->input->post('description');


        $this->db->set('name',$title);
        $this->db->set('description',$description);
        $this->db->where('id',$user_id);
        $this->db->update('construction_site');

        $result = array(
            'status' => 'FOUND',
            'response_code' => '1',
            'message' => "Item Posted Successfully"
        );
        echo json_encode($result);
    }

    public function get_construction_sites() {

        $products = $this->db->get('construction_site')->result_array();

        $result = array(
            'status' => 'FOUND',
            'response_code' => '1',
            'construction_sites_response' => $products
        );
        echo json_encode($result);
    }

    public function insert_construction_project() {

        $user_id       =$this->input->post('user_id');
        $title         =$this->input->post('title');
        $description   =$this->input->post('description');
        $address       =$this->input->post('address');
        $site_id       =$this->input->post('site_id');


        $this->db->set('site_id',$site_id);
        $this->db->set('name',$title);
        $this->db->set('description',$description);
        $this->db->set('address',$address);
        $this->db->set('created_by',$user_id);
        $this->db->insert('construction_project');

        $result = array(
            'status' => 'FOUND',
            'response_code' => '1',
            'message' => "Item Posted Successfully"
        );
        echo json_encode($result);
    }

    public function update_construction_project() {

        $project_id       =$this->input->post('project_id');
        $user_id       =$this->input->post('user_id');
        $title         =$this->input->post('title');
        $description   =$this->input->post('description');
        $address       =$this->input->post('address');
        $site_id       =$this->input->post('site_id');


        $this->db->set('site_id',$site_id);
        $this->db->set('name',$title);
        $this->db->set('description',$description);
        $this->db->set('address',$address);
        $this->db->set('created_by',$user_id);
        $this->db->where('id',$project_id);
        $this->db->update('construction_project');

        $result = array(
            'status' => 'FOUND',
            'response_code' => '1',
            'message' => "Item Posted Successfully"
        );
        echo json_encode($result);
    }

    public function get_construction_projects() {

        $products = $this->db->select("construction_project.*,construction_site.name as construction_site")
                             ->join("construction_site","construction_site.id = construction_project.site_id")
                             ->get ('construction_project')
            ->result_array();

        $result = array(
            'status' => 'FOUND',
            'response_code' => '1',
            'construction_project_response' => $products
        );
        echo json_encode($result);
    }

    public function dail_closings()
    {
        $data['campuses'] = $this->account->getCampuses();

        if ($this->input->post('to_date') == '')
            $today = date('Y-m-d');
        else
            $today=$this->input->post('to_date');

        $access = checkUserAccess();
        $acc = $access[0]['view_campus_closings'];
        $cam_closings = $access[0]['campus_closing_ids'];

        $this->db->select('*,closing_persons.campus_id as campus_id');
        $this->db->from('closing_persons');
        $this->db->join('campuses','campuses.campus_id = closing_persons.campus_id','left');
        $this->db->join('users','users.user_id = closing_persons.user_id','left');

        if(@$this->session->userdata('role') != 'Admin'){
            if ($acc == "1")
                $this->db->where_in('closing_persons.id',explode(",",$cam_closings));
            else
                $this->db->where('closing_persons.user_id = "'.$this->session->userdata('user_id').'" and active_status = 1');
        }
        else {
            $this->db->where('closing_persons.active_status = "1"');
        }

        $dataclose = $this->db->get()->result_array();

        $sq = 'select closing_perday.campus_id,campus_name,
                (select for_day from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
		        (select for_month from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,
		        MAX(for_year) as year from closing_perday 
		        left join campuses on campuses.campus_id = closing_perday.campus_id 
		        where (select count(*) from closing_persons where closing_persons.campus_id = closing_perday.campus_id and closing_persons.active_status = 1) > 0
		        GROUP by closing_perday.campus_id';
        $data['campusclosings'] = $this->db->query($sq)->result_array();

        $sq = 'select closing_perday.campus_id,campus_name,
		(select for_day   from closing_perday where campus_id = campuses.campus_id and checked_by = "1" order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
		(select for_month from closing_perday where campus_id = campuses.campus_id and checked_by = "1" order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,
		MAX(for_year) as year from closing_perday 
		left join campuses on campuses.campus_id = closing_perday.campus_id
		where (select count(*) from closing_persons where closing_persons.campus_id = closing_perday.campus_id and closing_persons.active_status = 1) > 0
        GROUP by closing_perday.campus_id';
        $data['campusclosingverified'] = $this->db->query($sq)->result_array();

        if(@$this->session->userdata('role') != 'Admin') {
            if ($acc == "1") {
                foreach ($data['campusclosings'] as $key => $cam) {
                    if (in_array($cam['campus_id'],explode(",",$cam_closings))) {
                        unset($data['campusclosings'][$key]);
                    }
                }
            }
            else {
                foreach ($data['campusclosings'] as $key => $cam) {
                    if ($cam['campus_id'] != $this->session->userdata('user_campus_id')) {
                        unset($data['campusclosings'][$key]);
                    }
                }
            }
        }

        $data['campusclosings'] = array_values($data['campusclosings']);

        foreach ($dataclose as $key=>$closing) {
            $sq = "select * from closing_perday where campus_id = '".$closing['campus_id']."' and for_month = '".date('m', strtotime($today))."' and for_day = '".date('d', strtotime($today))."'and for_year = '".date('Y', strtotime($today))."'";
            $closed = $this->db->query($sq)->result_array();

            if (count($closed)>0) {
                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('closing_id = "'.$closed[0]['campus_closing_id'].'"');
                $query = $this->db->get()->result_array();

                $value = array_sum(array_column($query,'actual_amount'));

                $dataclose[$key]['closing_amount'] = $value;
                $dataclose[$key]['closed_status'] = '1';
                $dataclose[$key]['closing_id'] = $closed[0]['id'];
                $dataclose[$key]['transaction_no'] = $closed[0]['transaction_no'];
                $dataclose[$key]['close_type'] = $closed[0]['close_type'];
                $dataclose[$key]['closed_by'] = $closed[0]['closed_by'];
                $dataclose[$key]['checked_by'] = $closed[0]['checked_by'];
                $dataclose[$key]['account_id'] = $closed[0]['account_id'];
            }
            else {
                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                $this->db->where('fee_pay_through = "college"');
                $this->db->where('actual_paid_date = "'.$today.'"');
                $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
                $query = $this->db->get()->result_array();

                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                $this->db->where('merged_challan is null');
                $this->db->where('fee_pay_through = "college"');
                $this->db->where('actual_paid_date = "'.$today.'"');
                $this->db->where('payments.paid = 1');
                $query2 = $this->db->get()->result_array();

                $yesterday = date('Y-m-d', strtotime($today. ' - 1 days'));

                $sq = "select * from closing_perday where campus_id = '".$closing['campus_id']."' and for_month = '".date('m', strtotime($yesterday))."' and for_day = '".date('d', strtotime($yesterday))."'and for_year = '".date('Y', strtotime($yesterday))."'";
                $closed = $this->db->query($sq)->result_array();

                if (count($closed)>0)  {

                    $this->db->select('*');
                    $this->db->from('payments');
                    $this->db->join('students','students.student_id = payments.student_id','left');
                    $this->db->join('courses','courses.course_id=students.course_id','inner');
                    $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                    $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                    $this->db->where('fee_pay_through = "college"');
                    $this->db->where('actual_paid_date = "'.$yesterday.'"');
                    $this->db->where('closing_id IS NULL');
                    $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
                    $query3 = $this->db->get()->result_array();

                    $this->db->select('*');
                    $this->db->from('payments');
                    $this->db->join('students','students.student_id = payments.student_id','left');
                    $this->db->join('courses','courses.course_id=students.course_id','inner');
                    $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                    $this->db->where('merged_challan is null');
                    $this->db->where('fee_pay_through = "college"');
                    $this->db->where('actual_paid_date = "'.$yesterday.'"');
                    $this->db->where('closing_id IS NULL');
                    $this->db->where('payments.paid = 1');
                    $query4 = $this->db->get()->result_array();
                    $final = array_merge($query3, $query4,$query, $query2);
                }

                else {
                    $final = array_merge($query, $query2);
                }

                $value = array_sum(array_column($final,'actual_amount'));

                $this->db->select('sum(asset_sales.sale_amount) as total');
                $this->db->from('asset_sales');
                $this->db->join('products','products.product_id = asset_sales.product_id','inner');
                $this->db->where("asset_sales.sold_date >= '$today 00:00:00' and asset_sales.sold_date <= '$today 23:59:59' and products.campus_id = '".$closing['campus_id']."'");
                $asset_sales_sum_today = $this->db->get()->result_array();

                $this->db->select('sum(asset_sales.sale_amount) as total');
                $this->db->from('asset_sales');
                $this->db->join('products','products.product_id = asset_sales.product_id','inner');
                $this->db->where("asset_sales.sold_date >= '$yesterday 00:00:00' and asset_sales.sold_date <= '$yesterday 23:59:59' and products.campus_id = '".$closing['campus_id']."' and closing_id IS NULL");
                $asset_sales_sum_yesterday = $this->db->get()->result_array();

                $asset_sale_amount = $asset_sales_sum_today[0]['total'] + $asset_sales_sum_yesterday[0]['total'];

                $this->db->select('sum(sales_payments.payment_amount) as total');
                $this->db->from('sales');
                $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
                $this->db->join('people','people.person_id  = sales.customer_id','inner');
                $this->db->where("sales.sale_time >= '$today 00:00:00' and sales.sale_time <= '$today 23:59:59' and sales.campus_id = '".$closing['campus_id']."'");
                $sales_sum = $this->db->get()->result_array();

                $this->db->select('sum(sales_payments.payment_amount) as total');
                $this->db->from('sales');
                $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
                $this->db->join('people','people.person_id  = sales.customer_id','inner');
                $this->db->where("sales.sale_time >= '$yesterday 00:00:00' and sales.sale_time <= '$yesterday 23:59:59' and sales.campus_id = '".$closing['campus_id']."'");
                $sales_sum_yesterday = $this->db->get()->result_array();

                $sale_amount = $sales_sum[0]['total'] + $sales_sum_yesterday[0]['total'];

                $dataclose[$key]['closing_amount'] = $value+$sale_amount+$asset_sale_amount;
                $dataclose[$key]['closed_status'] = '0';
                $dataclose[$key]['closing_id'] = '';
                $dataclose[$key]['close_type'] = '0';
                $dataclose[$key]['transaction_no'] = '';
                $dataclose[$key]['closed_by'] = '';
                $dataclose[$key]['checked_by'] = '';
                $dataclose[$key]['account_id'] = '';
            }
        }

        $data['closings']=$dataclose;
        $this->db->select('*');
        $this->db->from('accounts');
        $this->db->where('type = "1"');
        $data['accounts'] = $this->db->get()->result_array();

        $data['selected_date'] = $today;
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('closings/closingsheet', $data);
        $this->load->view('inc/footer');
    }

    public function all_campus_room_products()
    {

        $campus_id = $this->input->post('campus_id');
        $room_id = $this->input->post('room_id');
        $sub_room_id = $this->input->post('sub_room_id');
        if ($sub_room_id != "") {
            $array = array(
                'products.campus_id' => $campus_id,
                'products.room_id' => $room_id,
                'products.subroom_id' => $sub_room_id
            );
        }else{
            $array = array(
                'products.campus_id' => $campus_id,
                'products.room_id' => $room_id
            );
        }

        $this->db->select('campuses.campus_name,products.campus_id,rooms.room_name,subrooms.subroom_name,products.product_id,products.remaining_quantity,product_names.*,product_quantity as total_products,consume as consumed_quantity,products.purchase_slip,concat(users.first_name," ",users.last_name) as responsible_user,sale_quantity as sale_quantity');
        $this->db->from('products');
        $this->db->join('users','users.user_id=products.reponsilble_user_id','inner');
        $this->db->join('campuses','campuses.campus_id=products.campus_id','inner');
        $this->db->join('product_names','products.product_name_id=product_names.product_name_id','left');
        $this->db->join('rooms','rooms.room_id=products.room_id','left');
        $this->db->join('subrooms','subrooms.subroom_id=products.subroom_id','left');
        $this->db->where($array);
        $products = $this->db->get()->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'products_response'=>$products
        );
        echo json_encode($result);
    }
	
	public function recovery_management()
	{
		$this->db->select('*');
		$this->db->from('recovery_management');
		$this->db->join('designations','recovery_management.designation_id=designations.designation_id','INNER');
		$this->db->join('departments','designations.department_id=departments.department_id','INNER');
		$users = $this->db->get()->result_array();
		
		$recovery = array();
		$i=0;
		foreach($users as $user)
		{
			$campus_ids = explode(',',$user['campus_ids']);
			$this->db->where_in('campus_id',$campus_ids);
			$campuses = $this->db->get('campuses')->result_array();
			foreach($campuses as $campus)
			{
				$recovery[$i]['campuses'][] = $campus['campus_name'];
			}
			$recovery[$i]['department_name'] = $user['department_name'];
			$recovery[$i]['designation_name'] = $user['designation_name'];
			
			$rules = $this->db->get_where('users','(designation_id ="'.$user['designation_id'].'" or designation_id like "%'.$user['designation_id'].',%" or designation_id like "%,'.$user['designation_id'].'%") and status = "1"')->result_array();
										
			foreach($rules as $rule)
			{
				$recovery[$i]['user_id'] = $rule['user_id'];
				$recovery[$i]['user_name'] = $rule['first_name'].' '.$rule['last_name'];
			}
			
			$rules = $this->db->get_where('recovery_management_rules',array('recovery_management_id'=>$user['recovery_management_id']))->result_array();
			foreach($rules as $rule)
			{
				$recovery[$i]['comission'][] = 'Comission : '.$rule['start'].'%-'.$rule['end'].'% = Rs '.$rule['comission'];
			}
			
			$i++;
		}
		
		$result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'recovery_response'=>$recovery
        );
        echo json_encode($result);
	}

    public function getInventoryCampuses($user_id)
    {
        $user = $this->db->get_where('users',array('user_id'=>$user_id))->result_array();
        $access = $this->db->get_where('access', array('user_id'=>$user_id))->result_array();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);
        $this->db->select('campus_id,campus_name');
		if($user[0]['role']!='Admin')
		{
			$this->db->where_in('campus_id', $campus_ids);
		}
		$this->db->where('campuses.status',1);
		$campuses = $this->db->get('campuses')->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Found!',
            'campuses'=>$campuses
        );
        echo json_encode($result);
    }

    public function insertRoom()
    {
        $campus_id = $this->input->post('campus_id');
		$room_name = $this->input->post('room_name');
		$room_no = $this->input->post('room_no');
		$type = $this->input->post('type');
		
		$counter = count($room_name);
		
		for($i=0;$i<$counter;$i++)
		{
			$this->db->set('campus_id',$campus_id);
			$this->db->set('room_name',$room_name[$i]);
			$this->db->set('room_no',$room_no[$i]);
			$this->db->set('type',$type[$i]);
			$this->db->insert('rooms');
		}

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Room Inserted Successfully'
        );
        echo json_encode($result);
    }

    public function updateRoom($room_id)
    {
        $campus_id = $this->input->post('campus_id');
		$room_name = $this->input->post('room_name');
		$room_no = $this->input->post('room_no');
		$type = $this->input->post('type');
		
	
		
		$this->db->set('campus_id',$campus_id);
		$this->db->set('room_name',$room_name);
		$this->db->set('room_no',$room_no);
		$this->db->set('type',$type);
		$this->db->where('room_id',$room_id);
		
		$this->db->update('rooms');

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Room Updated Successfully'
        );
        echo json_encode($result);
    }

    public function getAllRooms($user_id)
    {
        $access = $this->db->get_where('access', array('user_id'=>$user_id))->result_array();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);

        $user = $this->db->get_where('users',array('user_id'=>$user_id))->result_array();

		$this->db->select('rooms.*,campuses.campus_name');
		$this->db->from('rooms');
		$this->db->join('campuses','campuses.campus_id=rooms.campus_id','INNER');
		if($user[0]['role']!='Admin')
		{
			$this->db->where_in('rooms.campus_id', $campus_ids);
		}
		$rooms = $this->db->get()->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Rooms Found',
            'rooms'=>$rooms
        );
        echo json_encode($result);
    }

    public function editRoom($room_id)
    {
        $room = $this->db->get_where('rooms',array('room_id'=>$room_id))->result_array();
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Room Found',
            'room'=>$room
        );
        echo json_encode($result);
    }

    public function deleteRoom($room_id)
    {
        $this->db->where('room_id',$room_id);
        $room = $this->db->delete('rooms');
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Room Deleted Successfully',
        );
        echo json_encode($result);
    }

    public function getCampusRooms($campus_id)
    {
        $rooms = $this->db->get_where('rooms',array('campus_id'=>$campus_id))->result_array();
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Rooms Found',
            'rooms'=>$rooms
        );
        echo json_encode($result);
    }

    public function insertSubroom()
    {
        $room_id = $this->input->post('room_id');
		$subroom_name = $this->input->post('subroom_name');
		
		$this->db->set('room_id',$room_id);
		$this->db->set('subroom_name',$subroom_name);
		$this->db->insert('subrooms');

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'SubRoom Inserted Successfully',
        );
        echo json_encode($result);
    }

    public function getAllSubrooms($user_id)
    {
        $access = $this->db->get_where('access', array('user_id'=>$user_id))->result_array();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);

        $user = $this->db->get_where('users',array('user_id'=>$user_id))->result_array();

        $this->db->select('rooms.*,subrooms.*,campuses.campus_name');
		$this->db->from('subrooms');
		$this->db->join('rooms','rooms.room_id=subrooms.room_id','INNER');
		$this->db->join('campuses','campuses.campus_id=rooms.campus_id','INNER');
		if($user[0]['role']!='Admin')
		{
			$this->db->where_in('campuses.campus_id', $campus_ids);
		}
		$subrooms = $this->db->get()->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Subrooms Found',
            'subrooms'=>$subrooms
        );
        echo json_encode($result);
    }

    public function editSubroom($subroom_id)
    {
        $this->db->select('*');
		$this->db->from('subrooms');
		$this->db->join('rooms','rooms.room_id=subrooms.room_id','INNER');
		$this->db->join('campuses','campuses.campus_id=rooms.campus_id','INNER');
		$this->db->where('subrooms.subroom_id',$subroom_id);
		$subroom = $this->db->get()->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Subroom Found',
            'subroom'=>$subroom
        );
        echo json_encode($result);
    }

    public function updateSubroom($subroom_id)
    {
        $room_id = $this->input->post('room_id');
		$subroom_name = $this->input->post('subroom_name');
		
		$this->db->set('room_id',$room_id);
		$this->db->set('subroom_name',$subroom_name);
		$this->db->where('subroom_id',$subroom_id);
		$this->db->update('subrooms');

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Subroom Updated Successfully'
        );
        echo json_encode($result);
    }

    public function deleteSubroom($subroom_id)
    {
        $this->db->where('subroom_id',$subroom_id);
		$this->db->delete('subrooms');
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Subroom Deleted Successfully',
        );
        echo json_encode($result);
    }

    public function getExpenseCategories()
    {
        $exp_categories = $this->db->get('expense_category')->result_array();
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Expense Categories Found',
            'expense_categories'=>$exp_categories
        );
        echo json_encode($result);
    }

    public function insertProductName()
    {
        $product_name = $this->input->post('product_name');
		$type = $this->input->post('type');
		$expense_category_id = $this->input->post('category_id');
		$head_category = $this->input->post('head_category');

		
		$this->db->set('product_name',$product_name);
		$this->db->set('type',$type);
		$this->db->set('expense_category_id',$expense_category_id);
        if($head_category != "" && $head_category != null)
        {
            $this->db->set('sub_of', $head_category);
            $this->db->insert('product_names');

            $this->db->set('has_sub',"1");
            $this->db->where('product_name_id',$head_category);
            $this->db->update('product_names');
        }
        else
        {
            $this->db->insert('product_names');
        }

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Product Name Inserted Successfully'
        );
        echo json_encode($result);
    }

    public function allProductNames()
    {
        $product_names = $this->db->select('product_names.*,expense_category.name')
            ->join('expense_category','expense_category.expense_category_id = product_names.expense_category_id','left')
            ->get_where('product_names','product_names.sub_of is NULL')->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Product Names Found',
            'product_names'=>$product_names
        );
        echo json_encode($result);
    }

    public function insertVendor()
    {
        $fields = $this->input->post();

		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'inventory_images/';
		
    	// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('image')) {
			$data = array('msg' => $this->upload->display_errors());
			$fields['image'] = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$fields['image'] = $data['upload_data']['file_name'];
			}
		}

		foreach(@$fields as $k=>$value){
			if($k!='product_name_ids' || $k!='user_id')
			{
				$this->db->set(''.$k.'', $value);
			}
		}
		$product_name_ids = $this->input->post('product_name_ids');
		$product_name_ids = implode(',',$product_name_ids);

		$this->db->set('product_name_ids',$product_name_ids);

		$this->db->set('created_by',$this->input->post('user_id'));
		$this->db->set('created_at',date('Y-m-d H:i:s'));

		$this->db->insert('vendors');
        
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Vendor Inserted Successfully'
        );
        echo json_encode($result);
    }

    public function getVendors($user_id)
    {
        $product_name_id = $this->input->post('product_name_id');
		$campus_id = $this->input->post('campus_id');

		$access = $this->db->get_where('access', array('user_id'=>$user_id))->result_array();
		$campus_ids = @explode(',',$access[0]['inventory_campuses']);

        $user = $this->db->get_where('users',array('user_id'=>$user_id))->result_array();

		//CHECK IT IS MAIN CATEGORY OR NOT
		$main = $this->db->get_where('product_names',array('product_name_id'=>$product_name_id))->result_array();
		if($main[0]['sub_of']==NULL)
		{
			$product_names = $this->db->get_where('product_names',array('sub_of'=>$main[0]['product_name_id']))->result_array();
			
			$product_name_ids = array();

			foreach($product_names as $product_name)
			{
				array_push($product_name_ids,$product_name['product_name_id']);
			}

			$this->db->select('vendors.*,campuses.campus_name as campus_name, CONCAT(users.first_name," ",users.last_name) as user_name');
			$this->db->from('vendors');
			$this->db->join('campuses','campuses.campus_id=vendors.campus_id','left');
			$this->db->join('users','vendors.created_by=users.user_id','left');
			foreach($product_name_ids as $single_product_name_id)
			{
				$this->db->or_where("find_in_set($single_product_name_id, vendors.product_name_ids)");
			}
			if($user[0]['role']!='Admin')
			{
				//$this->db->where_in('vendors.campus_id', $campus_ids);
			}
			if($campus_id!='')
			{
				$this->db->where('vendors.campus_id',$campus_id);
			}
			$vendors = $this->db->get()->result_array();
		}
		else
		{
			$this->db->select('vendors.*,campuses.campus_name as campus_name, CONCAT(users.first_name," ",users.last_name) as user_name');
			$this->db->from('vendors');
			$this->db->join('campuses','campuses.campus_id=vendors.campus_id','left');
			$this->db->join('users','vendors.created_by=users.user_id','left');
			if($campus_id!='')
			{
				$this->db->where('vendors.campus_id',$campus_id);
			}
			$this->db->where("find_in_set($product_name_id, vendors.product_name_ids)");
			if($user[0]['role']!='Admin')
			{
				//$this->db->where_in('vendors.campus_id', $campus_ids);
			}
			$vendors = $this->db->get()->result_array();
		}

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Vendors Found',
            'vendors'=>$vendors
        );
        echo json_encode($result);
    }

    public function get_vendor($vendor_id)
    {
        $vendor = $this->db->get_where('vendors',array('id'=>$vendor_id))->result_array();

        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Vendor Found',
            'vendor'=>$vendor
        );
        echo json_encode($result);
    }

    public function updateVendor($vendor_id)
    {
        $fields = $this->input->post();

		//load the helper
		$this->load->helper('form');

		//Configure
		//set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
		$config['upload_path'] = 'inventory_images/';
		
    	// set the filter image types
		$config['allowed_types'] = 'gif|jpg|png';
		
		//load the upload library
		$this->load->library('upload', $config);
    
		$this->upload->initialize($config);
		
		$this->upload->set_allowed_types('*');

		$data['upload_data'] = '';
    
		//if not successful, set the error message
		if (!$this->upload->do_upload('image')) {
			$data = array('msg' => $this->upload->display_errors());
			$fields['image'] = '';

		} 
		else 
		{ 
			//else, set the success message
      		$data['upload_data'] = $this->upload->data();
			if($data['upload_data']['file_name']){
				$fields['image'] = $data['upload_data']['file_name'];
			}
		}

		foreach(@$fields as $k=>$value){
			if($k!='product_name_ids' || $k!='user_id')
			{
				$this->db->set(''.$k.'', $value);
			}
		}
		$product_name_ids = $this->input->post('product_name_ids');
		$product_name_ids = implode(',',$product_name_ids);

		$this->db->set('product_name_ids',$product_name_ids);

		$this->db->set('created_by',$this->input->post('user_id'));
		$this->db->set('created_at',date('Y-m-d H:i:s'));
        $this->db->where('vendor_id',$vendor_id);
		$this->db->update('vendors');
        
        $result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Vendor Updated Successfully'
        );
        echo json_encode($result);
    }

    public function delete_vendor($vendor_id)
	{
		$this->db->where('id',$vendor_id);
		$this->db->delete('vendors');

		$result = array(
            'status'=>'FOUND',
            'response_code'=>'1',
            'message'=>'Vendor Deleted Successfully'
        );
        echo json_encode($result);
	}

}
//OLD API KEY
//$api_key = 'AAAAfu40qOo:APA91bFcwpeC0XC_xOV9jk3URK3J9tLLEzhiXCKNuEMUenRzN-zs0tuWURq0Jt5oimw1ldYPZSkKO8S8pY4CvNAqO7rpQt1l7TQ7k0ERxndOR3PsPx3Hk2xP0KrdtD0XSpvroJIZ9ywT';