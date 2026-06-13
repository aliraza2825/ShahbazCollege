<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class CollegeApi extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('student');
    }

    public function StudentLogin(){

        $roll_no = $this->input->post('roll_no');
        $password = md5($this->input->post('password'));

        $qry = "SELECT students.*, courses.course_name, classes.name as class_name, campuses.campus_name, student_documents.image FROM `students`  
        INNER JOIN courses ON students.course_id = courses.course_id  
        INNER JOIN classes ON classes.class_id = students.class_id 
        LEFT JOIN student_documents ON student_documents.student_id = students.student_id AND student_documents.type = 'Photo' 
        INNER JOIN campuses ON campuses.campus_id = classes.campus_id WHERE roll_no = '".$roll_no."' AND password = '".$password."' and students.status = 1";
        $query = $this->db->query($qry)->result_array();

        if(count($query)>0){
            if ($query[0]['status'] == '0' )
            {
                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '2',
                    'message' => 'Your are struck off from college. For further details make a call at 03174999859',
                    'response_courses' => array(),
                    'login_response' => array()
                );
                echo json_encode($result);
            }
            else {
                $cnic = $query[0]['cnic'];
                $courses = $this->db->select('courses.*')->join('courses', 'courses.course_id = students.course_id')->get_where("students", "cnic = '$cnic' and students.status = 1")->result_array();

                $this->db->set("student_id", $query[0]['student_id']);
                $this->db->set("login_time", date("Y-m-d H:i:s"));
                $this->db->set("logout_time", date("Y-m-d H:i:s"));
                $this->db->set("type", 'app');
                $this->db->insert("students_login_tracking");

                $result = array(
                    'status' => 'FOUND',
                    'response_code' => '1',
                    'message' => 'Login Successful',
                    'response_courses' => $courses,
                    'login_response' => $query
                );
                echo json_encode($result);
            }
        }
        else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'Login Failed',
                'login_response'=>'null'
            );
            echo json_encode($result);
        }
    }

    public function GuestUserLogin(){

        $name = $this->input->post('name');
        $phone   = $this->input->post('phone');
        $state   = $this->input->post('country');
        $country   = $this->input->post('province');
        $city   = $this->input->post('city');

        $this->db->set("first_name",$name);
        $this->db->set("last_name",$name);
        $this->db->set("father_name",$name);
        $this->db->set("mobile",$phone);
        $this->db->set("city",$city);
        $this->db->set("password",md5("12345"));
        $this->db->insert("students");

        $this->db->set("id",$this->db->insert_id());
        $this->db->set("name",$name);
        $this->db->set("phone",$phone);
        $this->db->set("country",$country);
        $this->db->set("state",$state);
        $this->db->set("city",$city);
        if($this->db->insert("guest_user")){
            $insert_id = $this->db->insert_id();
            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'Login Successful',
                'guest_login_response'=>$this->db->get_where("guest_user","id = '".$insert_id."'")->row()
            );
            echo json_encode($result);
        }
        else{
            $result = array(
                'status'=>'NOT FOUND',
                'response_code'=>'0',
                'message'=>'Login Failed',
                'guest_login_response'=>'null'
            );
            echo json_encode($result);
        }

    }

    public function GetDocuments(){

        $student_id = $this->input->post('student_id');
        $course_id=@$this->input->post('course_id');
        $student_fees = $this->db->get_where('students', array('student_id'=>$student_id))->result_array();
        if ($course_id) {
            $student_fees = $this->db->get_where('students', array('cnic' => $student_fees[0]['cnic'], 'course_id' => $course_id))->result_array();
            $student_id = $student_fees[0]['student_id'];
        }

        $qry = "SELECT * FROM `student_documents` WHERE student_id = $student_id  ORDER BY `student_documents`.`id`  DESC";
        $docs = $this->db->query($qry)->result_array();

        if(count($docs)>0){

            $this->db->set("student_id",$student_id);
            $this->db->set("login_time",date("Y-m-d H:i:s"));
            $this->db->set("logout_time",date("Y-m-d H:i:s"));
            $this->db->set("type",'app');
            $this->db->insert("students_login_tracking");
            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'docs_response'=>$docs
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'docs_response'=>$docs
            );
            echo json_encode($result);


        }
    }

    public function CheckAdmissionApplication(){

        $cnic = $this->input->post('cnic');

        $qry = "SELECT admission_applications.*, courses.course_name, campuses.campus_name, student_docs.student_image, student_docs.matriculation_result, student_docs.cnic_front, student_docs.cnic_back, student_docs.b_form, student_docs.signature FROM `admission_applications` 
INNER JOIN courses ON courses.course_id = admission_applications.course_id 
INNER JOIN campuses ON campuses.campus_id = admission_applications.campus_id 
INNER JOIN student_docs ON student_docs.application_id = admission_applications.application_id WHERE cnic = '".$cnic."' AND admission_applications.status != 4 GROUP BY application_id";
        $results = $this->db->query($qry)->result_array();

        if(count($results)>0){

            $result = array(
                'status'=>'IN_PROGRESS',
                'response_code'=>'1',
                'message'=>'Application already in progress',
                'continue_application_response'=>$results
            );
            echo json_encode($result);

        }else{

            $this->db->select(' * ');
            $this->db->from('students');
            $this->db->where(array('cnic'=>$cnic));
            $results_student = $this->db->get()->result_array();


            if(count($results_student)>0){

                $result = array(
                    'status'=>'REGISTERED',
                    'response_code'=>'2',
                    'message'=>'Student is already registered with institute',
                    'continue_application_response'=>$results
                );
                echo json_encode($result);

            }else{

                $result = array(
                    'status'=>'NOT REGISTERED',
                    'response_code'=>'0',
                    'message'=>'No Data Found',
                    'continue_application_response'=>$results
                );
                echo json_encode($result);


            }


        }
    }

    public function DeleteAdmissionApplication(){

        $application_id = $this->input->post('application_id');

        $results=$this->db->delete('admission_applications', array('application_id' => $application_id));

        if($results){

            $result = array(
                'status'=>'DELETED',
                'response_code'=>'1',
                'message'=>'Deleted Successfully',
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'FAILED',
                'response_code'=>'0',
                'message'=>'User not exist/Failed',
            );
            echo json_encode($result);


        }
    }

    public function GetAdmissionCourses(){

        $campus_id = $this->input->post("campus_id");
        if ($campus_id)
            $qry = "SELECT course_id FROM courses_sessions_mobile_app where campus_id = '$campus_id' group by course_id";
        else
            $qry = "SELECT course_id FROM courses_sessions_mobile_app group by course_id";

        $data = $this->db->query($qry)->result_array();
        $arr = array_column($data,"course_id");

        $this->db->select('*');
        $this->db->from('courses');
        $this->db->where_in("course_id",$arr);
        if (count($data) > 0)
            $courses = $this->db->get()->result_array();
        else
            $courses = array();

        foreach ($courses as $key=>$ab)
        {
            if($ab['course_type']=='Annual'){
                $qry1 = "SELECT course_id, subject_year, subject_semester FROM course_subjects WHERE course_id = '".$ab['course_id']."' GROUP BY subject_year";
            } else {
                $qry1 = "SELECT course_id, subject_year, subject_semester FROM course_subjects WHERE course_id = '".$ab['course_id']."' GROUP BY subject_semester";
            }
            $duration = $this->db->query($qry1)->result_array();

            if(count($duration)>0){
                $courses[$key]['duration_data'] = $duration;
            }else{
                $courses[$key]['duration_data'] = $duration;
            }
        }

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Data found!',
            'courses_data_response'=>$courses
        ) ;
        echo json_encode($result);

    }

    public function GetCampuses(){

        $study_type = $this->input->post("study_type");

        $qry = "SELECT DISTINCT(study_type.id), campuses.* FROM `lectures`
                INNER JOIN campuses ON campuses.campus_id = lectures.campus
                INNER JOIN study_type ON study_type.id = lectures.studytype = $study_type GROUP BY campus_id";
        $campuses = $this->db->query($qry)->result_array();

        if(count($campuses)>0){
            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'campuses_response'=>$campuses
            );
            echo json_encode($result);
        }
        else{
            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'campuses_response'=>$campuses
            );
            echo json_encode($result);
        }
    }

    public function GetStudyTypes(){


        $this->db->select(' * ');
        $this->db->from('study_type');
// 		$this->db->where(array('campus_id'=>$campus_id));
        $study_types = $this->db->get()->result_array();

        if(count($study_types)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'study_types_response'=>$study_types
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'study_types_response'=>$study_types
            );
            echo json_encode($result);


        }
    }

    public function GetShifts(){

        $study_type = $this->input->post('study_type');

        $qry = "SELECT DISTINCT(study_type.id) as studytype, shifts.*  FROM `lectures` LEFT JOIN shifts ON shifts.id = lectures.shift LEFT JOIN study_type ON study_type.id = lectures.studytype WHERE study_type.id = $study_type";
        $shifts = $this->db->query($qry)->result_array();

        if(count($shifts)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'shifts_response'=>$shifts
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'shits_response'=>$shifts
            );
            echo json_encode($result);


        }
    }

    public function GetPlans(){

        $course_id = $this->input->post('course_id');
        $session_id = $this->input->post('session_id');

        $this->db->select('*');
        $this->db->from('fee_rules');
        $this->db->where(array('course_id'=>$course_id,'session'=>$session_id));
        $plans = $this->db->get()->result_array();

        if(count($plans)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'plans_response'=>$plans
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'plans_response'=>$plans
            );
            echo json_encode($result);


        }

    }

    public function getChallanNo()
    {
        $random_number = rand(1000, 999999999);
        $check_challan_no = $this->db->get_where('payments', array('challan_no'=>$random_number))->result_array();
        if(count($check_challan_no)>0)
        {
            $random_number = $this->getChallanNo();
        }
        else
        {
            return $random_number;
        }
    }

    public function GetRegularRules(){

        $course_id = $this->input->post('course_id');
        $session = $this->input->post('session');

        $this->db->select(' * ');
        $this->db->from('fee_rules');
        $this->db->where(array('course_id'=>$course_id, 'session'=>$session));
        $plans = $this->db->get()->result_array();

        if(count($plans)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'regular_rules_response'=>$plans
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'regular_rules_response'=>$plans
            );
            echo json_encode($result);


        }

    }

    public function GetOnlineFeeRule(){

        $fee_rule_course_id = $this->input->post('fee_rule_course_id');

        $this->db->select(' * ');
        $this->db->from('online_fee_rules');
        $this->db->where(array('fee_rule_course_id'=>$fee_rule_course_id));
        $fee_rules = $this->db->get()->result_array();

        if(count($fee_rules)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'fee_rule_response'=>$fee_rules
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'fee_rule_response'=>$fee_rules
            );
            echo json_encode($result);


        }

    }

    public function SubmitDocuments(){
        $target_dir = "uploads/";

        $application_id = $this->input->post('application_id');
        $type = $this->input->post('type');
        $student_image_file_name = $target_dir .basename($_FILES["student_image"]["name"]);
        $matriculation_result_image_file_name = $target_dir .basename($_FILES["matriculation_result_image"]["name"]);

        $this->db->select('*');
        $this->db->from('student_docs');
        $this->db->where(array('student_docs.application_id'=>$application_id));
        $applicationDocuments = $this->db->get()->result_array();

        if(count($applicationDocuments)>0) {

            if (isset($_FILES["student_image"])){

                if ($type=='cnic'){

                    $cnic_front_image_file_name = $target_dir .basename($_FILES["cnic_front_image"]["name"]);
                    $cnic_back_image_file_name = $target_dir .basename($_FILES["cnic_back_image"]["name"]);

                    if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name) && move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name) && move_uploaded_file($_FILES["cnic_front_image"]["tmp_name"], $cnic_front_image_file_name) && move_uploaded_file($_FILES["cnic_back_image"]["tmp_name"], $cnic_back_image_file_name)){



                        $success = 1;
                        $message = "Successfully Uploaded";

                        $this->db->set(array(
                            'application_id'=>$application_id,
                            'student_image'=>$student_image_file_name,
                            'matriculation_result'=>$matriculation_result_image_file_name,
                            'cnic_front'=>$cnic_front_image_file_name,
                            'cnic_back'=>$cnic_back_image_file_name
                        ));
                        $this->db->where('application_id',$application_id);
                        $results=$this->db->update('student_docs');

                    }else{

                        $success = 1;
                        $message = "Error while uploading";

                    }
                } else {

                    $b_form_image_file_name = $target_dir .basename($_FILES["b_form_image"]["name"]);

                    if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name) && move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name) && move_uploaded_file($_FILES["b_form_image"]["tmp_name"], $b_form_image_file_name)){

                        $success = 1;
                        $message = "Successfully Uploaded";

                        $this->db->set(array(
                            'application_id'=>$application_id,
                            'student_image'=>$student_image_file_name,
                            'matriculation_result'=>$matriculation_result_image_file_name,
                            'b_form'=>$b_form_image_file_name
                        ));
                        $this->db->where('application_id',$application_id);
                        $results=$this->db->update('student_docs');

                    }else{

                        $success = 1;
                        $message = "Error while uploading";

                    }
                }
            }else{

                $success = 0;
                $message = "Required Field Missing";

            }

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>$success,
                'message'=>$message,
            );
            echo json_encode($result);

        } else {

            if (isset($_FILES["student_image"])){

                if ($type=='cnic'){

                    $cnic_front_image_file_name = $target_dir .basename($_FILES["cnic_front_image"]["name"]);
                    $cnic_back_image_file_name = $target_dir .basename($_FILES["cnic_back_image"]["name"]);

                    if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name) && move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name) && move_uploaded_file($_FILES["cnic_front_image"]["tmp_name"], $cnic_front_image_file_name) && move_uploaded_file($_FILES["cnic_back_image"]["tmp_name"], $cnic_back_image_file_name)){

                        $success = 1;
                        $message = "Successfully Uploaded";

                        $this->db->set(array(
                            'application_id'=>$application_id,
                            'student_image'=>$student_image_file_name,
                            'matriculation_result'=>$matriculation_result_image_file_name,
                            'cnic_front'=>$cnic_front_image_file_name,
                            'cnic_back'=>$cnic_back_image_file_name
                        ));
                        $results=$this->db->insert('student_docs');

                    }else{

                        $success = 1;
                        $message = "Error while uploading";

                    }
                } else {

                    $b_form_image_file_name = $target_dir .basename($_FILES["b_form_image"]["name"]);


                    if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name) && move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name) && move_uploaded_file($_FILES["b_form_image"]["tmp_name"], $b_form_image_file_name)){

                        $success = 1;
                        $message = "Successfully Uploaded";

                        $this->db->set(array(
                            'application_id'=>$application_id,
                            'student_image'=>$student_image_file_name,
                            'matriculation_result'=>$matriculation_result_image_file_name,
                            'b_form'=>$b_form_image_file_name
                        ));
                        $results=$this->db->insert('student_docs');

                    }else{

                        $success = 1;
                        $message = "Error while uploading";

                    }
                }
            }else{

                $success = 0;
                $message = "Required Field Missing";

            }

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>$success,
                'message'=>$message,
            );
            echo json_encode($result);

        }


    }

    public function UpdateDocuments()
    {
        $target_dir = "uploads/";

        $application_id = $this->input->post('application_id');
        $type = $this->input->post('type');





        // if (isset($_FILES["student_image"])){

        // if ($type=='cnic'){

        if (isset($_FILES["student_image"])){
            $student_image_file_name = $target_dir .basename($_FILES["student_image"]["name"]);
            if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name)){
                $this->db->set(array(
                    'student_image'=>$student_image_file_name,
                ));
                $this->db->where('application_id',$application_id);
                $results=$this->db->update('student_docs');
            }
        }

        if (isset($_FILES["matriculation_result_image"])){
            $matriculation_result_image_file_name = $target_dir .basename($_FILES["matriculation_result_image"]["name"]);
            if (move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name)){
                $this->db->set(array(
                    'matriculation_result'=>$matriculation_result_image_file_name,
                ));
                $this->db->where('application_id',$application_id);
                $results=$this->db->update('student_docs');
            }
        }

        if (isset($_FILES["cnic_front_image"])){
            $cnic_front_image_file_name = $target_dir .basename($_FILES["cnic_front_image"]["name"]);
            if (move_uploaded_file($_FILES["cnic_front_image"]["tmp_name"], $cnic_front_image_file_name)){
                $this->db->set(array(
                    'cnic_front'=>$cnic_front_image_file_name,
                ));
                $this->db->where('application_id',$application_id);
                $results=$this->db->update('student_docs');
            }
        }

        if (isset($_FILES["cnic_back_image"])){
            $cnic_back_image_file_name = $target_dir .basename($_FILES["cnic_back_image"]["name"]);
            if (move_uploaded_file($_FILES["cnic_back_image"]["tmp_name"], $cnic_back_image_file_name)){
                $this->db->set(array(
                    'cnic_back'=>$cnic_back_image_file_name,
                ));
                $this->db->where('application_id',$application_id);
                $results=$this->db->update('student_docs');
            }
        }

        if (isset($_FILES["b_form_image"])){
            $b_form_image_file_name = $target_dir .basename($_FILES["b_form_image"]["name"]);
            if (move_uploaded_file($_FILES["b_form_image"]["tmp_name"], $b_form_image_file_name)){
                $this->db->set(array(
                    'b_form'=>$b_form_image_file_name,
                ));
                $this->db->where('application_id',$application_id);
                $results=$this->db->update('student_docs');
            }
        }

        $success = 1;
        $message = "Successfully Uploaded";


        // if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name) && move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name) && move_uploaded_file($_FILES["cnic_front_image"]["tmp_name"], $cnic_front_image_file_name) && move_uploaded_file($_FILES["cnic_back_image"]["tmp_name"], $cnic_back_image_file_name)){



        //     $success = 1;
        //     $message = "Successfully Uploaded";

        //     $this->db->set(array(
        //         'application_id'=>$application_id,
        //         'student_image'=>$student_image_file_name,
        //         'matriculation_result'=>$matriculation_result_image_file_name,
        //         'cnic_front'=>$cnic_front_image_file_name,
        //         'cnic_back'=>$cnic_back_image_file_name
        //     ));
        //     $this->db->where('application_id',$application_id);
        //     $results=$this->db->update('student_docs');

        // }else{

        //     $success = 1;
        //     $message = "Error while uploading";

        // }
        // } else {

        //     $b_form_image_file_name = $target_dir .basename($_FILES["b_form_image"]["name"]);

        //     if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image_file_name) && move_uploaded_file($_FILES["matriculation_result_image"]["tmp_name"], $matriculation_result_image_file_name) && move_uploaded_file($_FILES["b_form_image"]["tmp_name"], $b_form_image_file_name)){

        //         $success = 1;
        //         $message = "Successfully Uploaded";

        //         $this->db->set(array(
        //             'application_id'=>$application_id,
        //             'student_image'=>$student_image_file_name,
        //             'matriculation_result'=>$matriculation_result_image_file_name,
        //             'b_form'=>$b_form_image_file_name
        //         ));
        //         $this->db->where('application_id',$application_id);
        //         $results=$this->db->update('student_docs');

        //     }else{

        //         $success = 1;
        //         $message = "Error while uploading";

        //     }
        // }
        // }else{

        //     $success = 0;
        //     $message = "Required Field Missing";

        // }

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>$results,
            'message'=>$message,
        );
        echo json_encode($result);
    }

    public function UpdateSignature()
    {
        $target_dir = "uploads/";

        $application_id = $this->input->post('application_id');
        $signatures = $target_dir .basename($_FILES["signatures"]["name"]);



        if (isset($_FILES["signatures"])){


            if (move_uploaded_file($_FILES["signatures"]["tmp_name"], $signatures)){

                $success = 1;
                $message = "Successfully Uploaded";

                $this->db->set(array(
                    'signature'=>$signatures
                ));
                $this->db->where('application_id',$application_id);
                $results=$this->db->update('student_docs');

            }else{

                $success = 1;
                $message = "Error while uploading";

            }

        }else{

            $success = 0;
            $message = "Required Field Missing";

        }

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>$success,
            'message'=>$message,
        );
        echo json_encode($result);
    }

    public function SubmitAdmissionApplication(){

        $application_id = $this->input->post('application_id');
        $course_id = $this->input->post('course_id');
        $campus_id = $this->input->post('campus_id');
        $first_name = $this->input->post('first_name');
        $last_name = $this->input->post('last_name');
        $father_name = $this->input->post('father_name');
        $plan_id = $this->input->post('plan_id');
        $gender = $this->input->post('gender');
        $qualification = $this->input->post('qualification');
        $caste = $this->input->post('caste');
        $religion = $this->input->post('religion');
        $email = $this->input->post('email');
        $cnic = $this->input->post('cnic');
        $blood_group = $this->input->post('blood_group');
        $date_of_birth = $this->input->post('date_of_birth');
        $city = $this->input->post('city');
        $address = $this->input->post('address');
        $mobile = $this->input->post('mobile');
        $emergency_no = $this->input->post('emergency_no');
        $district = $this->input->post('district');
        $tehsil = $this->input->post('tehsil');
        $mark_of_identification = $this->input->post('mark_of_identification');
        $place_of_birth = $this->input->post('place_of_birth');
        $board = $this->input->post('board');
        $shift = $this->input->post('shift');
        $study_type = $this->input->post('study_type');
        $fill_status = $this->input->post('fill_status');
        $status = $this->input->post('status');
        $reference_no = $this->input->post('reference_no');
        $device_id = $this->input->post('device_id');
        $class_id = $this->input->post('class_id');

        if($status==1){
            $qry = "SELECT * FROM `users` WHERE designation_id IN (7,4)";
            $device_array = $this->db->query($qry)->result_array();

            $ids = array();
            foreach($device_array as $device)
            {
                array_push($ids, $device['device_id']);
            }

            $url = 'https://fcm.googleapis.com/fcm/send';
            $api_key = 'AAAAfu40qOo:APA91bFcwpeC0XC_xOV9jk3URK3J9tLLEzhiXCKNuEMUenRzN-zs0tuWURq0Jt5oimw1ldYPZSkKO8S8pY4CvNAqO7rpQt1l7TQ7k0ERxndOR3PsPx3Hk2xP0KrdtD0XSpvroJIZ9ywT';
            $fields = array (
                // 'to'        => $device_array[0]["device_id"],
                'registration_ids' => $ids,
                'data' => array (
                    "title" => "$first_name $last_name Submitted Application",
                    "message" => "An admission application from $first_name $last_name. Please Review it."
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

        if($application_id==0){
            $this->db->set(array(
                'course_id'=>$course_id,
                'campus_id'=>$campus_id,
                'first_name'=>$first_name,
                'last_name'=>$last_name,
                'father_name'=>$father_name,
                'plan_id'=>$plan_id,
                'gender'=>$gender,
                'qualification'=>$qualification,
                'caste'=>$caste,
                'religion'=>$religion,
                'email'=>$email,
                'cnic'=>$cnic,
                'blood_group'=>$blood_group,
                'date_of_birth'=>$date_of_birth,
                'city'=>$city,
                'address'=>$address,
                'mobile'=>$mobile,
                'emergency_no'=>$emergency_no,
                'district'=>$district,
                'tehsil'=>$tehsil,
                'mark_of_identification'=>$mark_of_identification,
                'place_of_birth'=>$place_of_birth,
                'board'=>$board,
                'shift'=>$shift,
                'study_type'=>$study_type,
                'fill_status'=>$fill_status,
                'status'=>$status,
                'reference_no'=>$reference_no,
                'device_id'=>$device_id,
                'class_id'=>$class_id
            ));

            $results=$this->db->insert('admission_applications');
            $insert_id = $this->db->insert_id();
        } else {
            $this->db->set(array(
                'course_id'=>$course_id,
                'campus_id'=>$campus_id,
                'first_name'=>$first_name,
                'last_name'=>$last_name,
                'father_name'=>$father_name,
                'plan_id'=>$plan_id,
                'gender'=>$gender,
                'qualification'=>$qualification,
                'caste'=>$caste,
                'religion'=>$religion,
                'email'=>$email,
                'cnic'=>$cnic,
                'blood_group'=>$blood_group,
                'date_of_birth'=>$date_of_birth,
                'city'=>$city,
                'address'=>$address,
                'mobile'=>$mobile,
                'emergency_no'=>$emergency_no,
                'district'=>$district,
                'tehsil'=>$tehsil,
                'mark_of_identification'=>$mark_of_identification,
                'place_of_birth'=>$place_of_birth,
                'board'=>$board,
                'shift'=>$shift,
                'study_type'=>$study_type,
                'fill_status'=>$fill_status,
                'status'=>$status,
                'reference_no'=>$reference_no,
                'device_id'=>$device_id,
                'class_id'=>$class_id
            ));

            $this->db->where('application_id',$application_id);
            $results=$this->db->update('admission_applications');
            $insert_id = $application_id;
        }



        $this->db->select('*');
        $this->db->from('admission_applications');
        $this->db->where(array('application_id'=>$insert_id));
        $application = $this->db->get()->result_array();


        if($results){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Admission Fetched Successfully',
                'response'=>$insert_id,
                'application_response'=>$application
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'FAILED',
                'response_code'=>'0',
                'message'=>'Not submitted',
                'response'=>$insert_id,
                'application_response'=>$application
            );
            echo json_encode($result);


        }
    }

    public function InitiateTransaction(){

        $application_id = $this->input->post('application_id');
        $student_id = $this->input->post('student_id');
        $ipg_list = $this->input->post('ipg_list');
        $transaction_status = $this->input->post('transaction_status');
        $order_amount = $this->input->post('order_amount');
        $description = $this->input->post('description');
        $BAF_charge = $this->input->post('BAF_charge');
        $one_link_charge = $this->input->post('one_link_charge');
        $created_on = $this->input->post('created_on');
        $consumer_code = $this->input->post('consumer_code');
        $click2pay = $this->input->post('click2pay');
        $connect_pay_id = $this->input->post('connect_pay_id');
        $order_type = $this->input->post('order_type');
        $connect_pay_fee = $this->input->post('connect_pay_fee');
        $bill_url = $this->input->post('bill_url');
        $order_number = $this->input->post('order_number');
        $is_fee_applied = $this->input->post('is_fee_applied');
        $challans = $this->input->post('challans');

        $this->db->set(array(
            'application_id'=>$application_id,
            'student_id'=>$student_id,
            'ipg_list'=>$ipg_list,
            'transaction_status'=>$transaction_status,
            'order_amount'=>$order_amount,
            'description'=>$description,
            'BAF_charge'=>$BAF_charge,
            'one_link_charge'=>$one_link_charge,
            'created_on'=>$created_on,
            'consumer_code'=>$consumer_code,
            'click2pay'=>$click2pay,
            'connect_pay_id'=>$connect_pay_id,
            'order_type'=>$order_type,
            'connect_pay_fee'=>$connect_pay_fee,
            'bill_url'=>$bill_url,
            'order_number'=>$order_number,
            'is_fee_applied'=>$is_fee_applied,
            'challan_ids'=>$challans
        ));

        $this->db->insert('students_payments');
        $insert_id = $this->db->insert_id();
        $results=$this->db->get_where("students_payments","payment_id = '$insert_id'")->result_array();

        if ($insert_id){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Submitted Successfully',
                'initializalition_response'=>$results
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'FAILED',
                'response_code'=>'0',
                'message'=>'Not submitted',
                'initializalition_response'=>$results
            );
            echo json_encode($result);


        }

    }

    public function RegistrationTransaction(){

        $application_id = $this->input->post('application_id');
        $password = md5($this->input->post('password'));

        $qry = "SELECT * FROM `admission_applications` WHERE application_id = '".$application_id."'";
        $results = $this->db->query($qry)->result_array();

        $course_id = $results[0]['course_id'];
        $device_id = $results[0]['device_id'];
        $campus_id = $results[0]['campus_id'];
        $first_name = $results[0]['first_name'];
        $last_name = $results[0]['last_name'];
        $father_name = $results[0]['father_name'];
        $plan_id = $results[0]['plan_id'];
        $gender = $results[0]['gender'];
        $qualification = $results[0]['qualification'];
        $caste = $results[0]['caste'];
        $religion = $results[0]['religion'];
        $email = $results[0]['email'];
        $cnic = $results[0]['cnic'];
        $blood_group = $results[0]['blood_group'];
        $date_of_birth = $results[0]['date_of_birth'];
        $city = $results[0]['city'];
        $address = $results[0]['address'];
        $mobile = $results[0]['mobile'];
        $emergency_no = $results[0]['emergency_no'];
        $class_id = $results[0]['class_id'];
        $district = $results[0]['district'];
        $tehsil = $results[0]['tehsil'];
        $mark_of_identification = $results[0]['mark_of_identification'];
        $place_of_birth = $results[0]['place_of_birth'];
        $board = $results[0]['board'];
        $shift = $results[0]['shift'];
        $study_type = $results[0]['study_type'];

        if(count($results)>0){

            $this->db->set(array(
                'course_id'=>$course_id,
                'device_id'=>$device_id,
                'study_campus'=>$campus_id,
                'first_name'=>$first_name,
                'last_name'=>$last_name,
                'father_name'=>$father_name,
                'plan_id'=>$plan_id,
                'gender'=>$gender,
                'password'=>$password,
                'qualification'=>$qualification,
                'caste'=>$caste,
                'religion'=>$religion,
                'email'=>$email,
                'cnic'=>$cnic,
                'blood_group'=>$blood_group,
                'date_of_birth'=>$date_of_birth,
                'city'=>$city,
                'address'=>$address,
                'mobile'=>$mobile,
                'emergency_no'=>$emergency_no,
                'class_id'=>$class_id,
                'district'=>$district,
                'tehsil'=>$tehsil,
                'mark_of_identification'=>$mark_of_identification,
                'place_of_birth'=>$place_of_birth,
                'board'=>$board,
                'shift'=>$shift,
                'study_type'=>$study_type,
            ));


            $admission_result=$this->db->insert('students');
            $insert_id = $this->db->insert_id();

            $this->db->set(array(
                'status'=>'4',
            ));

            $this->db->where('application_id',$application_id);
            $status_results=$this->db->update('admission_applications');

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Submitted Successfully',
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'FAILED',
                'response_code'=>'0',
                'message'=>'Not submitted',
            );
            echo json_encode($result);


        }
    }

    public function GetIssueTypes(){

        $this->db->select(' * ');
        $this->db->from('issue_types');
        $issue_types = $this->db->get()->result_array();

        if(count($issue_types)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'issue_types_response'=>$issue_types
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'issue_types_response'=>$issue_types
            );
            echo json_encode($result);


        }

    }

    public function SubmitComplaint(){
        $category = $this->input->post('category');
        $message = $this->input->post('message');
        $student_id = $this->input->post('student_id');

        $this->db->set(array(
            'category'=>$category,
            'message'=>$message,
            'student_id'=>$student_id,
        ));


        $results=$this->db->insert('complaints');
        $insert_id = $this->db->insert_id();

        if($results){
            $this->db->set("student_id",$student_id);
            $this->db->set("login_time",date("Y-m-d H:i:s"));
            $this->db->set("logout_time",date("Y-m-d H:i:s"));
            $this->db->set("type",'app');
            $this->db->insert("students_login_tracking");
            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Complaint Submitted',
                'complaint_id'=>$insert_id,
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'FAILED',
                'response_code'=>'0',
                'message'=>'Unable to submit complaint',
                'complaint_id'=>$insert_id,
            );
            echo json_encode($result);


        }
    }

    public function GetComplaints(){

        $student_id = $this->input->post('student_id');

        $this->db->select(' * ');
        $this->db->from('complaints');
        $this->db->where(array('student_id'=>$student_id));
        $complaints = $this->db->get()->result_array();

        if(count($complaints)>0){
            $this->db->set("student_id",$student_id);
            $this->db->set("login_time",date("Y-m-d H:i:s"));
            $this->db->set("logout_time",date("Y-m-d H:i:s"));
            $this->db->set("type",'app');
            $this->db->insert("students_login_tracking");
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

    public function StartChat(){
        $category = $this->input->post('category');
        $message = $this->input->post('message');
        $student_id = $this->input->post('student_id');
        $question_id = $this->input->post('question_id');
        $chat_status = $this->input->post('chat_status');
        $assign_id = $this->input->post('assign_id');

        $this->db->set(array(
            'category'=>$category,
            'message'=>$message,
            'student_id'=>$student_id,
            'question_id'=>$question_id,
            'chat_status'=>$chat_status,
            'assign_id'=>$assign_id
        ));

        $results=$this->db->insert('chats');
        $insert_id = $this->db->insert_id();

        if($results){
            $this->db->set("student_id",$student_id);
            $this->db->set("login_time",date("Y-m-d H:i:s"));
            $this->db->set("logout_time",date("Y-m-d H:i:s"));
            $this->db->set("type",'app');
            $this->db->insert("students_login_tracking");
            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Chat Started',
                'chat_id'=>$insert_id,
            );
            echo json_encode($result);
        }else{
            $result = array(
                'status'=>'FAILED',
                'response_code'=>'0',
                'message'=>'Unable to start chat',
                'chat_id'=>$insert_id,
            );
            echo json_encode($result);
        }
    }

    public function GetChats(){

        $student_id = $this->input->post('student_id');

        $qry = "SELECT chats.*, issue_types.name as issue, questions.question FROM `chats` 
                        LEFT JOIN issue_types ON issue_types.id = chats.category 
                        LEFT JOIN questions ON questions.question_id = chats.question_id 
                    WHERE student_id = $student_id 
                ORDER BY chats.chat_id DESC";
        $chats = $this->db->query($qry)->result_array();

        if(count($chats)>0){
            $this->db->set("student_id",$student_id);
            $this->db->set("login_time",date("Y-m-d H:i:s"));
            $this->db->set("logout_time",date("Y-m-d H:i:s"));
            $this->db->set("type",'app');
            $this->db->insert("students_login_tracking");
            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'chats_response'=>$chats
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'chats_response'=>$chats
            );
            echo json_encode($result);


        }

    }

    public function GetChatTeachers(){

        $subject_id = $this->input->post('subject_id');

        $qry = "SELECT users.first_name, users.last_name, mobile_teachers_chat.teacher_id, GROUP_CONCAT(course_subjects.subject_name) as subject_name, course_subjects.course_subject_id FROM `mobile_teachers_chat` INNER JOIN users ON users.user_id = mobile_teachers_chat.teacher_id INNER JOIN course_subjects ON course_subjects.course_subject_id = mobile_teachers_chat.subject_id WHERE mobile_teachers_chat.subject_id = $subject_id GROUP BY mobile_teachers_chat.teacher_id";
        $teachers = $this->db->query($qry)->result_array();

        if(count($teachers)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'chat_teachers_response'=>$teachers
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'chat_teachers_response'=>$teachers
            );
            echo json_encode($result);


        }

    }

    public function DetermineLearnSession(){

        $user_id = $this->input->post('user_id');
        $course_id = $this->input->post('course_id');

        $qry = "SELECT IFNULL(MAX(session_id), 1) as current_session FROM `learn_result` WHERE user_id = $user_id AND course_id = $course_id";
        $data= $this->db->query($qry)->result_array();

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Data found!',
            'current_session'=>$data[0]['current_session']
        );
        echo json_encode($result);

    }

    public function GetLearnCourses(){

        $user_id = $this->input->post('user_id');
        $user_course_id = $this->input->post('user_course_id');
        $session_no = $this->input->post('session_no');
        $this->db->set("student_id",$user_id);
        $this->db->set("login_time",date("Y-m-d H:i:s"));
        $this->db->set("logout_time",date("Y-m-d H:i:s"));
        $this->db->set("type",'app');
        $this->db->insert("students_login_tracking");

        if ($user_id==0){
            $qry = "SELECT * FROM (SELECT courses.course_id, courses.course_name,courses.free,courses.demo ,courses.course_type, courses.course_duration_year, COUNT(DISTINCT(questions.question_id)) as total_questions, 0 as completed_questions FROM courses
                    LEFT JOIN questions ON questions.course_id = courses.course_id AND IF(courses.free = '1', questions.test_status IN (0,1), questions.test_status = 1)
                    GROUP BY courses.course_id) tbl WHERE total_questions > 1 and (free = '1' or demo = '1')";
        } else {
            $qry = "SELECT * FROM (SELECT courses.course_id, courses.course_name, courses.course_type, courses.course_duration_year, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM courses
                        LEFT JOIN learn_result ls ON ls.course_id = courses.course_id AND ls.user_id = $user_id AND ls.session_id = $session_no
                        LEFT JOIN questions ON questions.course_id = courses.course_id AND IF(courses.course_id = $user_course_id, questions.test_status IN (0,1), questions.test_status = 1)
                        GROUP BY courses.course_id) tbl WHERE total_questions > 1";
        }

        $data = $this->db->query($qry)->result_array();

        foreach ($data as $key=>$ab)
        {

            if($ab['course_type']=='Annual'){
                if ($user_id==0){
                    $qry1 = "SELECT * FROM (SELECT course_subjects.course_id, course_subjects.subject_semester, course_subjects.subject_year, COUNT(DISTINCT(questions.question_id)) as total_questions, 0 as completed_questions FROM course_subjects LEFT JOIN courses on courses.course_id =  course_subjects.course_id
                        LEFT JOIN questions ON questions.subject_id = course_subjects.course_subject_id AND IF(courses.free = '1', questions.test_status IN (0,1), questions.test_status = 1)
                        WHERE course_subjects.course_id = '".$ab['course_id']."'
                        GROUP BY course_subjects.subject_year) tbl WHERE total_questions >= 1";
                } else {
                    $qry1 = "SELECT * FROM (SELECT course_subjects.course_id, course_subjects.subject_semester, course_subjects.subject_year, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM course_subjects
                        LEFT JOIN learn_result ls ON ls.subject_id = course_subjects.course_subject_id AND ls.user_id = $user_id AND ls.session_id = $session_no
                        LEFT JOIN questions ON questions.subject_id = course_subjects.course_subject_id AND IF(course_subjects.course_id = $user_course_id, questions.test_status IN (0,1), questions.test_status = 1)
                        WHERE course_subjects.course_id = '".$ab['course_id']."'
                        GROUP BY course_subjects.subject_year) tbl WHERE total_questions >= 1";
                }
            } else {
                if ($user_id==0){
                    $qry1 = "SELECT * FROM (SELECT course_subjects.course_id, course_subjects.subject_semester, course_subjects.subject_year, COUNT(DISTINCT(questions.question_id)) as total_questions, 0 as completed_questions FROM course_subjects LEFT JOIN courses on courses.course_id =  course_subjects.course_id
                        LEFT JOIN questions ON questions.subject_id = course_subjects.course_subject_id AND IF(courses.free = '1', questions.test_status IN (0,1), questions.test_status = 1)
                        WHERE course_subjects.course_id = '".$ab['course_id']."'
                        GROUP BY course_subjects.subject_semester) tbl WHERE total_questions >= 1";
                } else {
                    $qry1 = "SELECT * FROM (SELECT course_subjects.course_id, course_subjects.subject_semester, course_subjects.subject_year, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM course_subjects
                        LEFT JOIN learn_result ls ON ls.subject_id = course_subjects.course_subject_id AND ls.user_id = $user_id AND ls.session_id = $session_no
                        LEFT JOIN questions ON questions.subject_id = course_subjects.course_subject_id
                        WHERE course_subjects.course_id = '".$ab['course_id']."'
                        GROUP BY course_subjects.subject_semester) tbl WHERE total_questions >= 1";
                }
            }
            $duration = $this->db->query($qry1)->result_array();


            if(count($duration)>0){

                $data[$key]['duration_data'] = $duration;
            }else{
                $data[$key]['duration_data'] = $duration;
            }
        }

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Data found!',
            'learn_courses_response'=>$data
        );
        echo json_encode($result);

    }

    public function GetCourseSummary(){

        $user_id = $this->input->post('user_id');
        $course_id = $this->input->post('course_id');
        $this->db->set("student_id",$user_id);
        $this->db->set("login_time",date("Y-m-d H:i:s"));
        $this->db->set("logout_time",date("Y-m-d H:i:s"));
        $this->db->set("type",'app');
        $this->db->insert("students_login_tracking");

        $qry = "SELECT * FROM (SELECT ls.session_id, courses.course_id, courses.course_name, courses.course_type, courses.course_duration_year, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM courses
                    LEFT JOIN learn_result ls ON ls.course_id = courses.course_id AND ls.user_id = $user_id
                    LEFT JOIN questions ON questions.course_id = courses.course_id
                    GROUP BY courses.course_id) tbl WHERE total_questions > 1 AND course_id = $course_id GROUP BY session_id";

        $data = $this->db->query($qry)->result_array();

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Data found!',
            'courses_summary_response'=>$data
        );
        echo json_encode($result);

    }

    public function GetCourseSubjects(){
        $courseID = $this->input->post('course_id');
        $user_id = $this->input->post('user_id');
        $session_no = $this->input->post('session_no');
        $courseTYPE = $this->input->post('course_type');
        $subject_year_semester = $this->db->get_where("students","student_id = '".$user_id."'")->row()->section;
        if ($subject_year_semester == 'Second Year')
            $subject_year_semester = 2;
        else
            $subject_year_semester = 1;

        $this->db->set("student_id",$user_id);
        $this->db->set("login_time",date("Y-m-d H:i:s"));
        $this->db->set("logout_time",date("Y-m-d H:i:s"));
        $this->db->set("type",'app');
        $this->db->insert("students_login_tracking");

        if ($user_id==0){
            if ($courseTYPE=='Annual'){
                $qry = "SELECT * FROM (SELECT course_subjects.course_subject_id, course_subjects.subject_name, course_subjects.subject_semester, course_subjects.subject_year,courses.free, COUNT(DISTINCT(questions.question_id)) as total_questions, 0 as completed_questions FROM course_subjects  LEFT JOIN courses on courses.course_id =  course_subjects.course_id
                        LEFT JOIN questions ON questions.subject_id = course_subjects.course_subject_id AND IF(courses.free = '1', questions.test_status IN (0,1), questions.test_status = 1)
                        WHERE course_subjects.course_id = $courseID AND course_subjects.subject_year = $subject_year_semester
                        GROUP BY course_subjects.course_subject_id) tbl WHERE total_questions >= 1";
            } else {
                $qry = "SELECT * FROM (SELECT course_subjects.course_subject_id, course_subjects.subject_name, course_subjects.subject_semester, course_subjects.subject_year,courses.free, COUNT(DISTINCT(questions.question_id)) as total_questions, 0 as completed_questions FROM course_subjects LEFT JOIN courses on courses.course_id =  course_subjects.course_id
                        LEFT JOIN questions ON questions.subject_id = course_subjects.course_subject_id AND IF(courses.free = '1', questions.test_status IN (0,1), questions.test_status = 1)
                        WHERE course_subjects.course_id = $courseID AND course_subjects.subject_semester = $subject_year_semester
                        GROUP BY course_subjects.course_subject_id) tbl WHERE total_questions >= 1";
            }
        } else {
            if ($courseTYPE=='Annual'){
                $qry = "SELECT * FROM (SELECT course_subjects.course_subject_id, course_subjects.subject_name, course_subjects.subject_semester, course_subjects.subject_year, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM course_subjects
                        LEFT JOIN learn_result ls ON ls.subject_id = course_subjects.course_subject_id AND ls.user_id = $user_id AND ls.session_id = $session_no
                        LEFT JOIN questions ON questions.subject_id = course_subjects.course_subject_id
                        WHERE course_subjects.course_id = $courseID AND course_subjects.subject_year = $subject_year_semester
                        GROUP BY course_subjects.course_subject_id) tbl WHERE total_questions >= 1";
            } else {
                $qry = "SELECT * FROM (SELECT course_subjects.course_subject_id, course_subjects.subject_name, course_subjects.subject_semester, course_subjects.subject_year, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM course_subjects
                        LEFT JOIN learn_result ls ON ls.subject_id = course_subjects.course_subject_id AND ls.user_id = $user_id AND ls.session_id = $session_no
                        LEFT JOIN questions ON questions.subject_id = course_subjects.course_subject_id
                        WHERE course_subjects.course_id = $courseID AND course_subjects.subject_semester = $subject_year_semester
                        GROUP BY course_subjects.course_subject_id) tbl WHERE total_questions >= 1";
            }
        }

        $data = $this->db->query($qry)->result_array();

        foreach ($data as $key=>$ab)
        {
            if ($user_id==0){
                if ($ab['free'] != "1")
                    $qry1 = "SELECT chapters.chapter_id, chapters.chapter_name, chapters.course_id, chapters.course_subject_id, COUNT(DISTINCT questions.topic_id) as total_topics FROM chapters LEFT JOIN topics ON topics.chapter_id = chapters.chapter_id LEFT JOIN questions ON questions.chapter_id = chapters.chapter_id AND questions.test_status = 1 WHERE chapters.course_subject_id = '".$ab['course_subject_id']."' GROUP BY chapters.chapter_id";
                else
                    $qry1 = "SELECT chapters.chapter_id, chapters.chapter_name, chapters.course_id, chapters.course_subject_id, COUNT(DISTINCT questions.topic_id) as total_topics FROM chapters LEFT JOIN topics ON topics.chapter_id = chapters.chapter_id LEFT JOIN questions ON questions.chapter_id = chapters.chapter_id WHERE chapters.course_subject_id = '".$ab['course_subject_id']."' GROUP BY chapters.chapter_id";
            } else {
                $qry1 = "SELECT chapters.chapter_id, chapters.chapter_name, chapters.course_id, chapters.course_subject_id, COUNT(DISTINCT questions.topic_id) as total_topics FROM chapters LEFT JOIN topics ON topics.chapter_id = chapters.chapter_id LEFT JOIN questions ON questions.chapter_id = chapters.chapter_id WHERE chapters.course_subject_id = '".$ab['course_subject_id']."' GROUP BY chapters.chapter_id";
            }
            $chapters= $this->db->query($qry1)->result_array();


            if(count($chapters)>0){

                foreach ($chapters as $k=>$a){
                    if ($user_id==0){
                        $chapters[$k]['completed_topics'] = 0;
                    } else {
                        $qry2 = "SELECT COUNT(*) as completed_topics FROM (SELECT (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM topics
                            LEFT JOIN learn_result ls ON ls.topic_id = topics.topic_id AND ls.user_id = $user_id AND ls.session_id = $session_no
                            LEFT JOIN questions ON questions.topic_id = topics.topic_id
                            WHERE topics.chapter_id = '".$a['chapter_id']."'
                            GROUP BY topics.topic_id) tbl WHERE completed_questions != 0";
                        $total = $this->db->query($qry2)->result_array();

                        $chapters[$k]['completed_topics'] = $total[0]['completed_topics'];
                    }
                }

                $data[$key]['chapters_data'] = $chapters;
            }else{

                foreach ($chapters as $k=>$a){
                    if ($user_id==0){
                        $chapters[$k]['completed_topics'] = 0;
                    } else {
                        $qry2 = "SELECT COUNT(*) as completed_topics FROM (SELECT (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM topics
                            LEFT JOIN learn_result ls ON ls.topic_id = topics.topic_id AND ls.user_id = $user_id AND ls.session_id = $session_no
                            LEFT JOIN questions ON questions.topic_id = topics.topic_id
                            WHERE topics.chapter_id = '".$a['chapter_id']."'
                            GROUP BY topics.topic_id) tbl WHERE completed_questions != 0";
                        $total = $this->db->query($qry2)->result_array();

                        $chapters[$k]['completed_topics'] = $total[0]['completed_topics'];
                    }
                }

                $data[$key]['chapters_data'] = $chapters;
            }
        }

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Data Found!',
            'course_subjects_response'=>$data
        );
        echo json_encode($result);
    }

    public function GetSubjectsSummary(){

        $subject_id = $this->input->post('subject_id');
        $user_id = $this->input->post('user_id');

        $this->db->set("student_id",$user_id);
        $this->db->set("login_time",date("Y-m-d H:i:s"));
        $this->db->set("logout_time",date("Y-m-d H:i:s"));
        $this->db->set("type",'app');
        $this->db->insert("students_login_tracking");

        $qry = "SELECT * FROM (SELECT IFNULL(ls.session_id, 1) as session_id , course_subjects.course_subject_id, course_subjects.subject_name, course_subjects.subject_semester, course_subjects.subject_year, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM course_subjects
                LEFT JOIN learn_result ls ON ls.subject_id = course_subjects.course_subject_id AND ls.user_id = $user_id 
                LEFT JOIN questions ON questions.subject_id = course_subjects.course_subject_id
                WHERE course_subjects.course_subject_id = $subject_id 
                GROUP BY ls.session_id) tbl WHERE total_questions >= 1";

        $data = $this->db->query($qry)->result_array();

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Data Found!',
            'subjects_summary_response'=>$data
        );
        echo json_encode($result);
    }

    public function GetTopics(){

        $chapter_id = $this->input->post('chapter_id');
        $user_id = $this->input->post('user_id');
        $session_no = $this->input->post('session_no');

        if ($user_id==0){
            $qry = "SELECT * FROM (SELECT topics.topic_id, topics.topic_name, topics.course_id, topics.chapter_id, topics.course_subject_id, topics.status, COUNT(DISTINCT(questions.question_id)) as total_questions, 0 as completed_questions FROM topics  LEFT JOIN courses on courses.course_id =  topics.course_id
                LEFT JOIN questions ON questions.topic_id = topics.topic_id AND IF(courses.free = '1', questions.test_status IN (0,1), questions.test_status = 1)
                WHERE topics.chapter_id = $chapter_id
                GROUP BY topics.topic_id) tbl WHERE total_questions >= 1";
        } else {
            $qry = "SELECT * FROM (SELECT topics.topic_id, topics.topic_name, topics.course_id, topics.chapter_id, topics.course_subject_id, topics.status, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM topics
                    LEFT JOIN learn_result ls ON ls.topic_id = topics.topic_id AND ls.user_id = $user_id AND ls.session_id = $session_no
                    LEFT JOIN questions ON questions.topic_id = topics.topic_id
                    WHERE topics.chapter_id = $chapter_id
                    GROUP BY topics.topic_id) tbl WHERE total_questions >= 1";
        }
        $data= $this->db->query($qry)->result_array();

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Data found!',
            'topics_data_response'=>$data
        );
        echo json_encode($result);
    }

    public function GetTopicsSummary(){

        $topic_id = $this->input->post('topic_id');
        $user_id = $this->input->post('user_id');

        $qry = "SELECT * FROM (SELECT IFNULL(ls.session_id, 1) as session_id, topics.topic_id, topics.topic_name, topics.course_id, topics.chapter_id, topics.course_subject_id, topics.status, COUNT(DISTINCT(questions.question_id)) as total_questions, (SELECT COUNT(*) FROM questions q WHERE FIND_IN_SET(q.question_id, GROUP_CONCAT(DISTINCT(ls.understand_questions)))) as completed_questions FROM topics
                LEFT JOIN learn_result ls ON ls.topic_id = topics.topic_id AND ls.user_id = $user_id
                LEFT JOIN questions ON questions.topic_id = topics.topic_id
                WHERE topics.topic_id = $topic_id
                GROUP BY ls.session_id) tbl WHERE total_questions > 1";
        $data= $this->db->query($qry)->result_array();

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Data found!',
            'topics_summary_response'=>$data
        );
        echo json_encode($result);
    }

    public function GetQuestionsLearn(){

        $topic_id = $this->input->post('topic_id');
        $user_id = $this->input->post('user_id');
        $quiz_type = $this->input->post('quiz_type');
        $session_no = $this->input->post('session_no');

        if($quiz_type=='mcqs'){
            if ($user_id==0){
                $qry = "SELECT * FROM `questions` LEFT JOIN topics on topics.topic_id =  questions.topic_id LEFT JOIN courses on courses.course_id =  topics.course_id WHERE IF(courses.free = '1', questions.test_status IN (0,1), questions.test_status = 1) AND questions.topic_id = '".$topic_id."' AND questions.type IN ('radio', 'multiple') ORDER BY RAND() LIMIT 5";
            } else {
                $qry = "SELECT * FROM `questions` WHERE NOT FIND_IN_SET(question_id, IFNULL((SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'MCQs' AND learn_result.topic_id = '".$topic_id."' AND learn_result.session_id = '".$session_no."'),0)) AND topic_id = '".$topic_id."' AND type IN ('radio', 'multiple') ORDER BY RAND() LIMIT 5";
            }
        } else if ($quiz_type=='short') {
            if ($user_id==0){
                $qry = "SELECT * FROM `questions` LEFT JOIN topics on topics.topic_id =  questions.topic_id LEFT JOIN courses on courses.course_id =  topics.course_id WHERE IF(courses.free = '1', questions.test_status IN (0,1), questions.test_status = 1) AND questions.topic_id = '".$topic_id."' AND questions.type IN ('short-question', 'long-question') ORDER BY RAND() LIMIT 5";
            } else {
                $qry = "SELECT * FROM `questions` WHERE NOT FIND_IN_SET(question_id, IFNULL((SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'short' AND learn_result.topic_id = '".$topic_id."' AND learn_result.session_id = '".$session_no."'),0)) AND topic_id = '".$topic_id."' AND type IN ('short-question', 'long-question') ORDER BY RAND() LIMIT 5";
            }
        } else {
            if ($user_id==0){
                $qry = "SELECT * FROM `questions` LEFT JOIN topics on topics.topic_id =  questions.topic_id LEFT JOIN courses on courses.course_id =  topics.course_id WHERE IF(courses.free = '1', questions.test_status IN (0,1), questions.test_status = 1) AND questions.topic_id = '".$topic_id."' AND questions.type IN ('word-meaning') ORDER BY RAND() LIMIT 5";
            } else {
                $qry = "SELECT * FROM `questions` WHERE NOT FIND_IN_SET(question_id, IFNULL((SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'meaning' AND learn_result.topic_id = '".$topic_id."'),0)) AND topic_id = '".$topic_id."' AND type IN ('word-meaning') ORDER BY RAND() LIMIT 5";
            }
        }
        $data = $this->db->query($qry)->result_array();

        foreach ($data as $k=>$a){

            $base_path = '/home/shahbazc/public_html/lahore-campus/recording/';

            if(file_exists($base_path.$data[$k]['question_id'].'.ogg')){
                $data[$k]['file_status'] = 'true';

            } else {
                $data[$k]['file_status'] = 'false';
            }
        }



        // $this->db->set('user_id', $user_id);
        // $this->db->set('topic_id', $topic_id);
        // $this->db->set('quiz_mode', $exam_mode);
        // $this->db->set('user_type', $user_type);
        // $this->db->set('total_questions', $questions_count);
        // $inserted = $this->db->insert('online_test_results_test');
        // $insert_id = $this->db->insert_id();

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Data found!',
            'learn_questions_response'=>$data
        );
        echo json_encode($result);

    }

    public function GetQuestionsLearned(){

        $topic_id = $this->input->post('topic_id');
        $user_id = $this->input->post('user_id');
        $quiz_type = $this->input->post('quiz_type');
        $session_no = $this->input->post('session_no');

        if($quiz_type=='mcqs'){
            $qry = "SELECT * FROM `questions` WHERE  FIND_IN_SET(question_id, IFNULL((SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'MCQs' AND learn_result.topic_id = '".$topic_id."' AND learn_result.session_id = '".$session_no."'),0)) AND topic_id = '".$topic_id."' AND type IN ('radio', 'multiple') ORDER BY RAND()";
        } else if ($quiz_type=='short') {
            $qry = "SELECT * FROM `questions` WHERE  FIND_IN_SET(question_id, IFNULL((SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'short' AND learn_result.topic_id = '".$topic_id."' AND learn_result.session_id = '".$session_no."'),0)) AND topic_id = '".$topic_id."' AND type IN ('short-question') ORDER BY RAND() ";
            //$qry = "SELECT * FROM `questions` WHERE NOT FIND_IN_SET(question_id, IFNULL((SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'short' AND learn_result.topic_id = '".$topic_id."'),0)) AND topic_id = '".$topic_id."' AND type IN ('short-question') ORDER BY RAND() LIMIT 5";
        } else {
            $qry = "SELECT * FROM `questions` WHERE  FIND_IN_SET(question_id, IFNULL((SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND questions_type = 'meaning' AND learn_result.topic_id = '".$topic_id."'),0)) AND topic_id = '".$topic_id."' AND type IN ('word-meaning') ORDER BY RAND() ";
        }
        // $qry = "SELECT * FROM `questions` WHERE NOT FIND_IN_SET(question_id, (SELECT understand_questions FROM learn_result WHERE learn_result.user_id = '".$user_id."')) AND topic_id = '".$topic_id."' AND type IN ('radio', 'multiple') ORDER BY RAND() LIMIT 5";
        $data = $this->db->query($qry)->result_array();

        // $this->db->set('user_id', $user_id);
        // $this->db->set('topic_id', $topic_id);
        // $this->db->set('quiz_mode', $exam_mode);
        // $this->db->set('user_type', $user_type);
        // $this->db->set('total_questions', $questions_count);
        // $inserted = $this->db->insert('online_test_results_test');
        // $insert_id = $this->db->insert_id();

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Data found!',
            'learned_questions_response'=>$data
        );
        echo json_encode($result);

    }

    public function PostLearnQuestions(){
        $user_id = $this->input->post('user_id');
        $user_type = $this->input->post('user_type');
        $course_id = $this->input->post('course_id');
        $subject_id = $this->input->post('subject_id');
        $chapter_id = $this->input->post('chapter_id');
        $topic_id = $this->input->post('topic_id');
        $session_no = $this->input->post('session_no');
        $question_type = $this->input->post('question_type');
        $understand_questions = $this->input->post('understand_questions');

        $qry = "SELECT * FROM learn_result WHERE learn_result.user_id = '".$user_id."' AND learn_result.topic_id = '".$topic_id."' AND questions_type = '".$question_type."' AND session_id = '".$session_no."'";
        $data = $this->db->query($qry)->result_array();

        if (count($data)>0){
            // $this->db->set('user_id', $user_id);
            // $this->db->set('topic_id', $topic_id);
            // $this->db->set('user_type', $user_type);
            // $this->db->set('session_id', 1);
            // $this->db->set('questions_type', $question_type);
            $this->db->set('understand_questions', $data[0]['understand_questions'].','.$understand_questions);
            $this->db->where(array('id'=>$data[0]['id']));
            $updated=$this->db->update('learn_result');
        } else {
            $this->db->set('user_id', $user_id);
            $this->db->set('course_id', $course_id);
            $this->db->set('subject_id', $subject_id);
            $this->db->set('chapter_id', $chapter_id);
            $this->db->set('topic_id', $topic_id);
            $this->db->set('user_type', $user_type);
            $this->db->set('session_id', $session_no);
            $this->db->set('questions_type', $question_type);
            $this->db->set('understand_questions', $understand_questions);
            $inserted = $this->db->insert('learn_result');
        }

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Posted_successfully'
        );
        echo json_encode($result);
    }

    public function GetCourses(){

        $qry = "SELECT * FROM courses";
        $courses = $this->db->query($qry)->result_array();

        if(count($courses)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'courses_response'=>$courses
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'courses_response'=>$courses
            );
            echo json_encode($result);


        }

    }

    public function GetApplicationData(){

        $type = $this->input->post('type');
        $qry = "SELECT * FROM mobile_advertisement WHERE type = '".$type."'";
        $applicationData = $this->db->query($qry)->result_array();

        if(count($applicationData)>0){
            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'application_data_response'=>$applicationData
            );
            echo json_encode($result);
        }else{
            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'application_data_response'=>$applicationData
            );
            echo json_encode($result);
        }
    }

    public function GetApplicationStudentData(){

        $user_id = $this->input->post('user_id');
        $type = $this->input->post('type');

        $this->db->set("student_id",$user_id);
        $this->db->set("login_time",date("Y-m-d H:i:s"));
        $this->db->set("logout_time",date("Y-m-d H:i:s"));
        $this->db->set("type",'app');
        $this->db->insert("students_login_tracking");

        if ($type == 'student'){
            $advertisement_ids = $this->db->select("shown_advertisement")->get_where("student_shown_advertisements","student_id = '$user_id'")->result_array();
        }else
            $advertisement_ids = $this->db->select("shown_advertisement")->get_where("student_shown_advertisements","guest_id = '$user_id'")->result_array();

        $names = array_column($advertisement_ids, 'shown_advertisement');
        $this->db->select("*");
        $this->db->from("mobile_advertisement");
        $this->db->where("type","advertisement");
        if (count($names) > 0)
            $this->db->where_not_in("id",$names);
        $applicationData = $this->db->get()->result_array();

        foreach ($applicationData as $advertisement){
            if ($type == 'student')
                $this->db->set("student_id",$user_id);
            else
                $this->db->set("guest_id",$user_id);

            $this->db->set("shown_advertisement",$advertisement['id']);
            $this->db->insert("student_shown_advertisements");
        }

        if(count($applicationData)>0){
            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'application_data_response'=>$applicationData
            );
            echo json_encode($result);
        }else{
            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'application_data_response'=>$applicationData
            );
            echo json_encode($result);
        }
    }

    public function GetBookData(){

        $topic_id = $this->input->post('topic_id');

        $qry = "SELECT * FROM books WHERE topic_id = $topic_id";
        $bookData = $this->db->query($qry)->result_array();

        if(count($bookData)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'learn_book_response'=>$bookData
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'learn_book_response'=>$bookData
            );
            echo json_encode($result);


        }

    }

    public function PostInterview(){

        $campus_id = $this->input->post('campus_id');
        $name = $this->input->post('name');
        $address = $this->input->post('address');
        $qualification = $this->input->post('qualification');
        $other_current_job = $this->input->post('other_current_job');
        $previous_experience = $this->input->post('previous_experience');
        $gender = $this->input->post('gender');
        $marital_status = $this->input->post('marital_status');
        $job_post_wanted = $this->input->post('job_post_wanted');
        $cell_number = $this->input->post('cell_number');

        $this->db->set('campus_id', $campus_id);
        $this->db->set('name', $name);
        $this->db->set('address', $address);
        $this->db->set('qualification', $qualification);
        $this->db->set('other_current_job', $other_current_job);
        $this->db->set('previous_experience', $previous_experience);
        $this->db->set('gender', $gender);
        $this->db->set('marital_status', $marital_status);
        $this->db->set('job_post_wanted', $job_post_wanted);
        $this->db->set('cell_number', $cell_number);
        $inserted = $this->db->insert('interview');
        $insert_id = $this->db->insert_id();


        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Posted_successfully',
            'interview_id'=>$insert_id
        );
        echo json_encode($result);

    }

    public function UpdateCV()
    {
        $target_dir = "uploads/";

        $interview_id = $this->input->post('interview_id');
        $cv = $target_dir .basename($_FILES["cv"]["name"]);



        if (isset($_FILES["cv"])){


            if (move_uploaded_file($_FILES["cv"]["tmp_name"], $cv)){

                $success = 1;
                $message = "Successfully Uploaded";

                $this->db->set(array(
                    'cv'=>$cv
                ));
                $this->db->where('interview_id',$interview_id);
                $results=$this->db->update('interview');

            }else{

                $success = 1;
                $message = "Error while uploading";

            }

        }else{

            $success = 0;
            $message = "Required Field Missing";

        }

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>$success,
            'message'=>$message,
        );
        echo json_encode($result);
    }

    public function GetProducts(){

        $qry = "SELECT * FROM sale_products";
        $courses = $this->db->query($qry)->result_array();

        if(count($courses)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'products_response'=>$courses
            );
            echo json_encode($result);

        }else{


            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'products_response'=>$courses
            );
            echo json_encode($result);


        }

    }

    public function Placeorder(){

        $campus_id = $this->input->post('student_id');
        $name = $this->input->post('receive_type');
        $address = $this->input->post('address');
        $products = $this->input->post('products');
        $qtys = explode(",",$this->input->post('qtys'));
        $phone_no = $this->input->post('phone_no');


        $this->db->set('student_id', $campus_id);
        $this->db->set('receive_type', $name);
        $this->db->set('delivery_address', $address);
        $this->db->set('phone_no', $phone_no);

        $inserted = $this->db->insert('mobile_app_orders');
        $insert_id = $this->db->insert_id();
        foreach(explode(",",$products) as $key=>$prod)
        {
            $this->db->set('order_id', $insert_id);
            $this->db->set('qty', $qtys[$key]);
            $this->db->set('product_id', $prod);

            $this->db->insert('order_products');
        }


        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Posted_successfully',
            'order_id'=>"ord-".$insert_id
        );
        echo json_encode($result);
    }

    public function GetSessions(){

        $course_id = $this->input->post('course_id');

        $qry = "SELECT class_id FROM courses_sessions_mobile_app where course_id = '$course_id' group by class_id";

        $data = $this->db->query($qry)->result_array();
        $arr = array_column($data,"class_id");

        $this->db->select('*');
        $this->db->from('classes');
        $this->db->where_in("class_id",$arr);
        if (count($data) > 0)
            $courses = $this->db->get()->result_array();
        else
            $courses = array();

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Data found!',
            'sessions_response'=>$courses
        ) ;
        echo json_encode($result);

    }

    public function GetFeePlans(){

        $course_id = $this->input->post('course_id');

        $qry = "SELECT class_id FROM courses_sessions_mobile_app where course_id = '$course_id' group by class_id";

        $data = $this->db->query($qry)->result_array();
        $arr = array_column($data,"class_id");

        $this->db->select('*');
        $this->db->from('classes');
        $this->db->where_in("class_id",$arr);
        $classes = $this->db->get()->result_array();
        $sessions = array_column($classes,"session");

        $this->db->select('*');
        $this->db->from('fee_rules');
        $this->db->where(array('course_id'=>$course_id,'last_date>='=>date("Y-m-d")));
        $this->db->where_in("session",$sessions);
        $plans = $this->db->get()->result_array();

        if(count($plans)>0){

            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'fee_plans_response'=>$plans
            );
            echo json_encode($result);

        }else{
            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'fee_plans_response'=>$plans
            );
            echo json_encode($result);
        }

    }

    public function GetLiveLecture()
    {
        $student_id = $this->input->post('student_id');
        $this->db->set("student_id",$student_id);
        $this->db->set("login_time",date("Y-m-d H:i:s"));
        $this->db->set("logout_time",date("Y-m-d H:i:s"));
        $this->db->set("type",'app');
        $this->db->insert("students_login_tracking");

        $data = $this->db->join("classes",'classes.class_id = students.class_id')->get_where("students","student_id = '$student_id'")->row();

        $this->db->select('*');
        $this->db->from('lectures');
        $this->db->where(array('studytype' => '4'));
        $this->db->where("FIND_IN_SET('session',$data->session) IS NOT NULL");

        $plans = $this->db->get()->result_array();

        if(count($plans)>0){
            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'lectures_response'=>$plans
            );
            echo json_encode($result);
        }
        else{
            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'lectures_response'=>$plans
            );
            echo json_encode($result);
        }
    }

    public function GetFeeDetails(){

        $student_id=$this->input->post('user_id');
        $course_id=@$this->input->post('course_id');
        $student_fees = $this->db->get_where('students', array('student_id'=>$student_id))->result_array();
        if ($course_id) {
            $student_fees = $this->db->get_where('students', array('cnic' => $student_fees[0]['cnic'], 'course_id' => $course_id))->result_array();
            $student_id = $student_fees[0]['student_id'];
        }
        $student_fee = $student_fees[0]['total_fee'];
        $active_student = $this->student->payment_paid($student_id);
        if(count($active_student)>0)
        {
            $this->db->set("student_id",$student_id);
            $this->db->set("login_time",date("Y-m-d H:i:s"));
            $this->db->set("logout_time",date("Y-m-d H:i:s"));
            $this->db->set("type",'app');
            $this->db->insert("students_login_tracking");

            $qry = "SELECT DISTINCT `merged_challan`, discount as discount FROM `payments` WHERE student_id=$student_id and merged_challan is not null GROUP by merged_challan UNION ALL SELECT DISTINCT `merged_challan`, sum(discount) as discount FROM `payments` WHERE student_id=$student_id and merged_challan is null";
            $query = $this->db->query($qry)->result_array();

            if (count($query)>0) {

                $tt=0;
                foreach($query as $discs){
                    $tt+= $discs['discount'];
                }
                $data['discountfee']=$tt;
            }else{
                $data['discountfee'] = '0';

            }
            $data['discount'] = $this->student->getStudentDiscount($student_id);
            $data['paid_fee'] = $this->student->getStudentPaidFee($student_id);
            $data['paid_fee'] =$data['paid_fee'][0]['paid_fee']-$data['discountfee'];
            $data['remaining_fee'] = $this->student->getStudentRemainingFee($student_id);
            $data['fee_should_pay'] = $this->student->getStudentFeeShouldPay($student_id);
            $data['consulation_fee'] = $this->student->getStudentConsulationFee($student_id);
            $data['consulation_fee_should_pay'] = $this->student->getStudentConsulationFeeShouldPay($student_id);
            $data['consulation_fee_paid'] = $this->student->getStudentConsulationFeePaid($student_id);
            $data['consulation_fee_unpaid'] = $this->student->getStudentConsulationFeeUnPaid($student_id);
            $data['removed_fine'] = $this->student->getStudentRemovedFine($student_id);
            $data['fine_should_pay'] = $this->student->getStudentFineShouldPay($student_id);
            $data['fine_paid'] = $this->student->getStudentFinePaid($student_id);
            $data['total_fine'] = $this->student->getStudentTotalFine($student_id);

            $result = array(
                'status'=>'FOUND',
                'response_code'=>'1',
                'message'=>'fee_structure',
                'discount'=>$data['discountfee'],
                'total_fee'=>$student_fee,
                'paid_fee'=> $data['paid_fee'],
                'remaining_fee'=> $data['remaining_fee'],
                'removed_fine'=> $data['removed_fine'],
                'fine_paid'=> $data['total_fine'],
                'total_fine'=> $data['total_fine']+$data['removed_fine'],
                'response_challan'=>$active_student
            );
            echo json_encode($result);
        }
        else
        {
            $result=array(
                'status'=>'ERROR',
                'response_code'=>'2',
                'message'=>'No Fee Structure Found',
                'response_challan'=>array()
            );
            echo json_encode($result);
        }
    }

    public function InitiatePaymentTransaction(){
        $paypro_challan = $this->getPayproID();
        $student_id = $this->input->post('student_id');
        $challans = $this->input->post('challans');
        foreach (explode(",",$challans) as $pay_challan) {
            $data = array(
//                'merged_challan' => $paypro_challan,
//                'paid_challans' => $challans,
                'actual_amount' => $this->input->post('actual_amount'),
                'discount' => $this->input->post('discount'),
                'paid_date' => date('Y-m-d'),
                'actual_paid_date' => date('Y-m-d'),
                'college_fee' => 0,
                'fee_pay_through' => "pay_pro",
                'fine_amount' => $this->input->post('fine_amount'),
                'submitted_fee_campus_id' => "0"
            );
            $this->db->set($data);
            $this->db->where("challan_no",$pay_challan);
            $this->db->update("payments");
        }
        $student = $this->db->get_where("students","student_id = '$student_id'")->row();
        $number = ($student->mobile != "" && $student->mobile != NULL) ? $student->mobile : $student->emergency_no;
        if ($number == "" || $number == NULL)
            $number = "03168042977";
        $bill_url = $this->generate_paypro($this->input->post('actual_amount'),$student->first_name.' '.$student->last_name,$number,$paypro_challan,$student_id,$challans);

        $result = array(
            'status'=>'SUCCESS',
            'response_code'=>'1',
            'message'=>'Submitted Successfully',
            'initializalition_response'=>$bill_url
        );
        echo json_encode($result);
        /*-----TESTING-----*/
        $variables = json_encode($this->input->post());
        $this->db->set('test',$variables);
        $this->db->set('link',$bill_url);
        $this->db->insert('ztesting');
        /*-----TESTING-----*/
    }

    public function getPayproID()
    {
        $random_number = rand(1000, 999999999);
        $check_challan_no = $this->db->get_where('students_payments', array('order_number'=>$random_number))->result_array();
        if(count($check_challan_no)>0)   {
            $random_number = $this->getChallanNo();
        }
        else {
            return $random_number;
        }
    }

    public function generate_paypro($amount,$name,$mobile,$order_no,$student_id,$challans)
    {
        $date = date('d-m-Y');
        $total_order = array();
        $merchant = array("MerchantId"=>"SCOP","MerchantPassword"=>"Live@shahbaz21");
        $order = array("OrderNumber"=>"$order_no","OrderAmount"=>"$amount"
        ,"OrderDueDate"=>"$date","OrderAmountWithinDueDate"=>"$amount"
        ,"OrderAmountAfterDueDate"=>"$amount"
        ,"OrderType"=>"Service","OrderTypeId"=>"Service"
        ,"IssueDate"=>"$date","OrderExpireAfterSeconds"=>"0"
        ,"CustomerName"=>"$name","CustomerMobile"=>"$mobile"
        ,"CustomerEmail"=>"","CustomerAddress"=>""
        );
        array_push($total_order,$merchant);
        array_push($total_order,$order);
        $headers = array(
            'Content-Type:application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.paypro.com.pk/cpay/co?");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($total_order));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        $response = array_values(json_decode($result, true));
        if ($response[0]['Status'] == "00") {
            $ipg_main_list = $response[1]['IPGList'];
            $application_id = "";
            $ipg_list = $ipg_main_list;
            $transaction_status = $response[1]['TransactionStatus'];
            $order_amount = $amount;
            $description = $response[1]['TransactionStatus'];
            $BAF_charge = $response[1]['BAFCharge'];
            $one_link_charge = $response[1]['1LinkCharge'];
            $created_on = date('Y-m-d');
            $consumer_code = $response[1]['ConsumerCode'];
            $click2pay = $response[1]['Click2Pay'];
            $connect_pay_id = $response[1]['ConnectPayId'];
            $order_type = $response[1]['FetchOrderType'];
            $connect_pay_fee = $response[1]['ConnectPayFee'];
            $bill_url = $response[1]['BillUrl'];
            $order_number = $response[1]['OrderNumber'];
            $is_fee_applied = $response[1]['IsFeeApplied'];
            $this->db->set(array(
                'application_id' => $application_id,
                'student_id' => $student_id,
                'ipg_list' => json_encode($ipg_list),
                'transaction_status' => $transaction_status,
                'order_amount' => $order_amount,
                'description' => $description,
                'BAF_charge' => $BAF_charge,
                'one_link_charge' => $one_link_charge,
                'created_on' => $created_on,
                'consumer_code' => $consumer_code,
                'click2pay' => $click2pay,
                'connect_pay_id' => $connect_pay_id,
                'order_type' => $order_type,
                'connect_pay_fee' => $connect_pay_fee,
                'bill_url' => $bill_url,
                'order_number' => $order_number,
                'is_fee_applied' => $is_fee_applied,
                'challan_ids' => $challans
            ));
            $this->db->insert('students_payments');
            $insert_id = $this->db->insert_id();
            return $click2pay;
        }
    }

    public function GetResults(){

        $cnic = $this->input->post("cnic");
        $this->db->select('*');
        $this->db->from('punjab_council_roll_number');
        $this->db->where('cnic', $cnic);
        $results = $this->db->get()->result_array();

        if(count($results)>0){
            $result = array(
                'status'=>'SUCCESS',
                'response_code'=>'1',
                'message'=>'Found',
                'results_response'=>$results
            );
            echo json_encode($result);
        }
        else{
            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'results_response'=>$results
            );
            echo json_encode($result);
        }
    }

    public function generate_syllabus(){

        $rule_id = $this->input->post("rule_id");
        $days = $this->input->post("days");
        $student_id = $this->input->post("student_id");
        $rule = $this->db->select("*")->get_where('study_rules_app',"id = '$rule_id'")->row();
        $student = $this->db->get_where('students',"student_id = '$student_id'")->row();
        $days=explode(",",$days);
        $max_id = $this->db->select('IFNULL(MAX(count), 0) as count')->get_where("student_syllabus_details","student_id = '$student_id' and course_id = '$rule->course_id'")->row()->count;
        $this->db->set('student_id',$student_id);
        $this->db->set('course_id',$rule->course_id);
        $this->db->set('count',($max_id+1));
        $this->db->set('percentage','0');
        $this->db->insert('student_syllabus_details');
        $insert_id = $this->db->insert_id();

        $today = date("Y-m-d");
        $last = date("Y-m-d",strtotime("+".$rule->months." months", strtotime($today)));

        $datetime1 = new DateTime($today);
        $datetime2 = new DateTime($last);
        $interval = $datetime1->diff($datetime2);
        $interval_days = $interval->days;
        $months_and_dates = array();
        $quizgaps = $rule->months;

        $x = 0;
        $quizes = 0;
        for($day = 1;$day<$interval_days;$day++) {
            $newday = $day-1;
            $internal_date = date('Y-m-d', strtotime("{$today} + {$newday} days"));
            $this_day = date('Y-m-d', strtotime($internal_date));
            foreach ($days as $tday) {
                if ($this->isTuesday($internal_date, strtolower($tday))) {
                    $da = array("date"=>'',"day"=>'','is_quiz'=>'');
                    array_push($months_and_dates,$da);
                    if(($x+1)%$quizgaps==0) {
                        $months_and_dates[$x]['date'] = $this_day;
                        $months_and_dates[$x]['day'] = strtoupper($tday);
                        $months_and_dates[$x]['is_quiz'] = 1;
                        $quizes++;
                    }
                    else {
                        $months_and_dates[$x]['date'] = $this_day;
                        $months_and_dates[$x]['day'] = strtoupper($tday);
                        $months_and_dates[$x]['is_quiz'] = 0;
                    }
                    $x++;
                }
                else
                    echo $this->isTuesday($internal_date, strtolower($tday));
            }
        }
        if ($student->section == 'Second Year')
            $class = 2;
        else
            $class = 1;

        $subjects = $this->db->get_where("course_subjects","course_id = '$rule->course_id' and subject_year = '$class'")->result_array();
        foreach ($subjects as $subject){
            $word_meanings = $this->db
                ->order_by('topics.chapter_id','ASC')
                ->join("topics","topics.topic_id = questions.topic_id")
                ->get_where("questions","topics.course_subject_id = '".$subject['course_subject_id']."'")
                ->result_array();

            $per = round(count($word_meanings) / (count($months_and_dates)-$quizes));
            $index = 0;
            $quiz_questions = '';
            foreach ($months_and_dates as $key=>$months_and_date){
                if ($months_and_date['is_quiz'] == 1){
                    $this->db->set('student_syllabus_details_id',$insert_id);
                    $this->db->set('student_id',$student_id);
                    $this->db->set('course_id',$rule->course_id);
                    $this->db->set('subject_id',$subject['course_subject_id']);
                    $this->db->set('day',$months_and_date['day']);
                    $this->db->set('date',$months_and_date['date']);
                    $this->db->set('topic_ids',$quiz_questions);
                    $this->db->set('is_quiz',1);
                    $this->db->insert('student_syllabus');
                    $quiz_questions = '';
                }else {
                    $mcqs = '';
                    $tot = $index + $per;
                    if ($tot > count($word_meanings))
                        $tot = count($word_meanings);
                    for ($x = $index; $x < $tot; $x++) {
                        $mcqs .= $word_meanings[$x]['question_id'] . ',';
                        $quiz_questions .= $word_meanings[$x]['question_id'] . ',';
                        $index++;
                    }
                    $this->db->set('student_syllabus_details_id',$insert_id);
                    $this->db->set('student_id',$student_id);
                    $this->db->set('course_id',$rule->course_id);
                    $this->db->set('subject_id',$subject['course_subject_id']);
                    $this->db->set('day',$months_and_date['day']);
                    $this->db->set('date',$months_and_date['date']);
                    $this->db->set('topic_ids',$mcqs);
                    $this->db->set('is_quiz',0);
                    $this->db->insert('student_syllabus');
                }
            }
            if ($months_and_dates[count($months_and_dates)-1]['is_quiz'] != 1){
                $cur_date = date('Y-m-d', strtotime("+1 day", strtotime($months_and_dates[count($months_and_dates)-1]['date'])));
                $this->db->set('student_syllabus_details_id',$insert_id);
                $this->db->set('student_id',$student_id);
                $this->db->set('course_id',$rule->course_id);
                $this->db->set('subject_id',$subject['course_subject_id']);
                $this->db->set('day','');
                $this->db->set('date',$cur_date);
                $this->db->set('topic_ids',$quiz_questions);
                $this->db->set('is_quiz',1);
                $this->db->insert('student_syllabus');
            }
        }

        $result = array(
            'status'=>'Success',
            'response_code'=>'1',
            'message'=>'Success'
        );
        echo json_encode($result);
    }

    function isTuesday($date,$day) {

        if ($day == 'monday')
        {
            $day = '1';
        }
        elseif ($day == 'tuesday')
        {
            $day = '2';
        }
        elseif ($day == 'wednesday')
        {
            $day = '3';
        }
        elseif ($day == 'thursday')
        {
            $day = '4';
        }elseif ($day == 'friday')
        {
            $day = '5';
        }elseif ($day == 'saturday')
        {
            $day = '6';
        }elseif ($day == 'sunday')
        {
            $day = '0';
        }

        return date('w', strtotime($date)) === $day;
    }

    public function GetStudentStudyPlan(){

        $student_id = $this->input->post("student_id");
        $course_id = $this->input->post("course_id");
        $max_id = $this->db->select('MAX(id) as id')->get_where("student_syllabus_details","student_id = '$student_id' and course_id = '$course_id'")->row();
        if ($max_id){
            $this->db->select('course_subjects.*');
            $this->db->from('student_syllabus');
            $this->db->join('courses','courses.course_id = student_syllabus.course_id');
            $this->db->join('course_subjects','course_subjects.course_subject_id = student_syllabus.subject_id');
            $this->db->where('student_syllabus_details_id', $max_id->id);
            $this->db->group_by('student_syllabus.subject_id');
            $results = $this->db->get()->result_array();

            foreach ($results as $key=>$result){

                $total = $this->db->select('count(*) as total')->get_where("student_syllabus","student_id = '$student_id' and subject_id = '".$result['course_subject_id']."' and student_syllabus_details_id='$max_id->id'")->row();
                $pending = $this->db->select('count(*) as total')->get_where("student_syllabus","student_id = '$student_id' and subject_id = '".$result['course_subject_id']."' and student_syllabus_details_id='$max_id->id' and status = 'pending'")->row();
                $completed = $this->db->select('count(*) as total')->get_where("student_syllabus","student_id = '$student_id' and subject_id = '".$result['course_subject_id']."' and student_syllabus_details_id='$max_id->id' and status = 'completed'")->row();

                $results[$key]['perc_done'] = number_format(($completed->total/$total->total)*100);
            }

            $result = array(
                'status' => 'SUCCESS',
                'response_code' => '1',
                'message' => 'Found',
                'study_courses_response' => $results,
                'study_rules_response' => $this->db->get_where('study_rules_app','status = 1')->result_array()
            );
            echo json_encode($result);
        }
        else{
            $result = array(
                'status'=>'Failed',
                'response_code'=>'0',
                'message'=>'No Data Found',
                'study_courses_response'=>array()
            );
            echo json_encode($result);
        }
    }

    public function GetStudentStudyPlanDetails(){
        $student_id = $this->input->post("student_id");
        $subject_id = $this->input->post("subject_id");
        $syllabus_id = $this->input->post("syllabus_id");

            $this->db->select('*,student_syllabus.status as status,"" as topic_names');
            $this->db->from('student_syllabus');
            $this->db->join('courses','courses.course_id = student_syllabus.course_id');
            $this->db->join('course_subjects','course_subjects.course_subject_id = student_syllabus.subject_id');
            $this->db->where('student_syllabus_details_id', $syllabus_id);
            $this->db->where('student_id', $student_id);
            $this->db->where('subject_id', $subject_id);
            $this->db->order_by('student_syllabus.id', "ASC");
            $results = $this->db->get()->result_array();

            foreach($results as $key=>$result){
                $this->db->select('topics.topic_name');
                $this->db->from('questions');
                $this->db->join('topics','topics.topic_id = questions.topic_id');
                $this->db->where_in('question_id',explode(",",$result['topic_ids']));
                $this->db->group_by('topics.topic_id');
                $topics = $this->db->get()->result_array();
                $tps = array_column($topics, 'topic_name');
                $results[$key]['topic_names'] = implode(",",$tps);
            }

            $result = array(
                'status' => 'SUCCESS',
                'response_code' => '1',
                'message' => 'Found',
                'study_topics_response' => $results
            );
            echo json_encode($result);
    }
}