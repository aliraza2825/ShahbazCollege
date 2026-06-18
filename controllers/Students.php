<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Students extends CI_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('clas');
        $this->load->model('student');
        $this->load->model('contractor');
        $this->load->library('upload');
        $this->load->model('council');
        require_once("vendor/autoload.php");
    }
    
    public function update_payment_comment(){
        
        $council_sequence = $this->input->post('sequence_id');
        $exam_sequence = $this->input->post('payment_comment');
        $comment = $this->input->post('comment');
        $old_record = $this->db->get_where('payments','id = ')->row_array();
        
        
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
        
        $fee = $this->db->join('courses','courses.course_id = exam_sequence.course_id')->get_where("exam_sequence","id = '".$exam_sequence."'")->row_array();
        $course_type = $fee['course_type'] == 'Annual' ? 'Year':$fee['course_type'];
        
        $this->db->set("payment_comment",'This fee for next exam # '.$fee['first_year'].' '.$this->getOrdinal($fee['class']).' '.$course_type);
        $this->db->set("council_sequence_id",$council_sequence);
        $this->db->set("exam_sequence_id",$exam_sequence);
        $this->db->set("exam_sequence_no",$fee['first_year']);
        $this->db->set("exam_class",$fee['class']);
        $this->db->where('id', $this->input->post('id'));
        $this->db->update('payments');
        
        if($comment || $image){
            $this->db->set("fee_id",$this->input->post('id'));
            $this->db->set("comment",$comment);
            $this->db->set("paid_on_date",date('Y-m-d'));
            $this->db->set("clear_status",0);
            $this->db->set("add_by",$this->session->userdata('name'));
            $this->db->set("image",$image);
            $this->db->insert('fees_remarks');
        }
        
        $comment = 'This fee for next exam # '.$fee['first_year'].' '.$this->getOrdinal($fee['class']).' '.$course_type;
        echo json_encode([
            'status'          => true,
            'payment_comment' => $comment
        ]);
    }
    
    function getOrdinal($number)
    {
        $suffixes = ['th','st','nd','rd','th','th','th','th','th','th'];
    
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }
    
        return $number . $suffixes[$number % 10];
    }

    public function insert()
    {
        //CHECK STUDENT CNIC IS ALREADY EXIST OR NOT
        $studentNic = $this->student->checkStudentNIC($this->input->post('cnic'),$this->input->post('course_id'));
        if(count($studentNic)>0)
        {
            $this->session->set_flashdata('error', 'Student with CNIC '.$this->input->post('cnic').' is already added');
            redirect('students/add_student');
        }

        if(@$this->input->post('books_1'))
        {
            $books_1 = $this->input->post('books_1');
        }
        else
        {
            $books_1 = '0';
        }

        if(@$this->input->post('books_2'))
        {
            $books_2 = $this->input->post('books_2');
        }
        else
        {
            $books_2 = '0';
        }

        if(@$this->input->post('student_card'))
        {
            $student_card = $this->input->post('student_card');
        }
        else
        {
            $student_card = '0';
        }

        if($this->input->post('contractor_id')==0 || $this->input->post('contract_id')==0)
        {
            $contractor_id = 0;
            $contract_id = 0;
        }
        else
        {
            $contractor_id = $this->input->post('contractor_id');
            $contract_id = $this->input->post('contract_id');
        }

        $course = $this->db->get_where("courses","course_id = '".$this->input->post('course_id')."'")->row();
        $class = $this->db->get_where("classes","class_id = '".$this->input->post('class_id')."'")->row();
        $campus = $this->db->get_where("campuses","campus_id = '".$class->campus_id."'")->row();
        $student_count = $this->db->select('max(count) as count')->get_where("students","class_id = '".$this->input->post('class_id')."'")->row();
        if ($student_count == null)
        {
            $student_count = 1;
        }
        else
        {
            $student_count=$student_count->count+1;
        }
            
        $roll_no = $student_count.'-'.$class->badge_no.'-'.$course->course_code.'-'.$campus->roll_no_code;
        

        $data = array(
            'course_id'			=> $this->input->post('course_id'),
            'study_campus'		=> $this->input->post('study_campus'),
            'first_name'		=> $this->input->post('first_name'),
            'last_name'			=> $this->input->post('last_name'),
            'father_name'		=> $this->input->post('father_name'),
            'gender'			=> $this->input->post('gender'),
            'caste'				=> $this->input->post('caste'),
            'religion'			=> $this->input->post('religion'),
            'qualification'		=> $this->input->post('qualification'),
            'class_id'			=> $this->input->post('class_id'),
            'roll_no'			=> $roll_no,
            'email'				=> $this->input->post('email'),
            'cnic'				=> $this->input->post('cnic'),
            'date_of_birth'		=> $this->input->post('date_of_birth'),
            'count'		        => $student_count,
            'district'		    => $this->input->post('district'),
            'tehsil'		    => $this->input->post('tehsil'),
            'mark_of_identification'		=> $this->input->post('mark_of_identification'),
            'place_of_birth'		=> $this->input->post('place_of_birth'),
            'registration_date'	=> $this->input->post('registration_date'),
            'total_fee'			=> $this->input->post('total_fee'),
            'current_session_fee'  => 0,
            'blood_group'		=> $this->input->post('blood_group'),
            'city'				=> $this->input->post('city'),
            'address'			=> $this->input->post('address'),
            'mobile'			=> $this->input->post('mobile'),
            'emergency_no'		=> $this->input->post('emergency_no'),
            'status' 			=> $this->input->post('status'),
            'contractor_id'		=> $contractor_id,
            'contract_id'		=> $contract_id,
            'board'				=> $this->input->post('board'),
            'section'			=> $this->input->post('section'),
            'shift'				=> $this->input->post('shift'),
            'study_type'		=> $this->input->post('study_type'),
            'books_1'			=> $books_1,
            'books_2'			=> $books_2,
            'student_card'		=> $student_card,
            'password'			=> md5($this->input->post('password')),
            'notes'				=> json_encode($this->input->post('note')),
            'add_by'			=> $this->input->post('add_by'),
            'last_edit'			=> $this->input->post('last_edit'),
            'entry_date'		=> date('Y-m-d'),
            'reference_user_id' => $this->input->post('reference_user_id')
        );

        if($data['class_id']=='' || $data['class_id']==0)
        {
            $this->session->set_flashdata('error','Please Try again to add the student.');
            redirect('students/add_student');
        }
        $student_id = $this->student->storeStudent($data);

        //ADD MACHINE ID
        $campus_id = $this->db->get_where('campuses',array('campus_id'=>$this->input->post('campus_id')))->result_array();
        $sql = 'SELECT machine_id FROM machine_data WHERE campus_id='.$campus_id[0]['campus_id'].' ORDER BY machine_id DESC LIMIT 1';
        $query = $this->db->query($sql)->result_array();
        $last_machine_id = substr($query[0]['machine_id'], 0, -2);
        $last_machine_id = $last_machine_id+1;

        $this->db->set('teacher_student_id',$student_id);
        $this->db->set('machine_id',$last_machine_id.$campus_id[0]['campus_code']);
        $this->db->set('type','student');
        $this->db->set('campus_id',$campus_id[0]['campus_id']);
        $this->db->insert('machine_data');

        $this->session->set_flashdata('message', 'Student added successfully!');
        redirect('students/payments/'.$student_id);
    }

    public function update($id)
    {
        $class_id = $this->input->post('class_id');

        //CHECK CLASS ADD / EDIT STUDENT
        $check_class = $this->db->get_where('classes',array('class_id'=>$class_id))->result_array();
        //CHECEEK STUDENT CURRENT CLASS
        $current_class = $this->db->get_where('students',array('student_id'=>$id))->result_array();

        if($current_class[0]['class_id']==$class_id)
        {
            $check_request = $this->db->get_where('update_student_requests',array('student_id'=>$id, 'ok_by_admin'=>0))->result_array();
            if(count($check_request)>0)
            {
                $this->session->set_flashdata('error', 'This Student update Request is already exist. Please contact Control Center to clear previous request.');
                redirect('students/edit_student/'.$id);
            }

            if(count($this->input->post())==0)
            {
                redirect('students/all_students');
            }
            if(@$this->input->post('books_1'))
            {
                $books_1 = $this->input->post('books_1');
            }
            else
            {
                $books_1 = '0';
            }
            if(@$this->input->post('books_2'))
            {
                $books_2 = $this->input->post('books_2');
            }
            else
            {
                $books_2 = '0';
            }
            if(@$this->input->post('student_card'))
            {
                $student_card = $this->input->post('student_card');
            }
            else
            {
                $student_card = '0';
            }

            if($this->input->post('password'))
            {
                $password = md5($this->input->post('password'));
            }
            else
            {
                $password = $this->input->post('old_password');
            }

            if($this->input->post('contractor_id')==0 || $this->input->post('contract_id')==0)
            {
                $contractor_id = 0;
                $contract_id = 0;
            }
            else
            {
                $contractor_id = $this->input->post('contractor_id');
                $contract_id = $this->input->post('contract_id');
            }

            $data = array(
                'course_id'			        => $this->input->post('course_id'),
                'study_campus'		        => $this->input->post('study_campus'),
                'first_name'		        => $this->input->post('first_name'),
                'last_name'			        => $this->input->post('last_name'),
                'father_name'		        => $this->input->post('father_name'),
                'gender'			        => $this->input->post('gender'),
                'caste'				        => $this->input->post('caste'),
                'religion'			        => $this->input->post('religion'),
                'qualification'		        => $this->input->post('qualification'),
                'class_id'			        => $this->input->post('class_id'),
                'email'				        => $this->input->post('email'),
                'cnic'				        => $this->input->post('cnic'),
                'roll_no'			        => $this->input->post('roll_no'),
                'date_of_birth'		        => $this->input->post('date_of_birth'),
                'district'		            => $this->input->post('district'),
                'tehsil'		            => $this->input->post('tehsil'),
                'mark_of_identification'	=> $this->input->post('mark_of_identification'),
                'place_of_birth'		    => $this->input->post('place_of_birth'),
                'registration_date'	        => $this->input->post('registration_date'),
                'total_fee'			        => $this->input->post('total_fee'),
                'blood_group'		        => $this->input->post('blood_group'),
                'city'				        => $this->input->post('city'),
                'address'			        => $this->input->post('address'),
                'mobile'			        => $this->input->post('mobile'),
                'emergency_no'		        => $this->input->post('emergency_no'),
                'status' 			        => $this->input->post('status'),
                'contractor_id'		        => $contractor_id,
                'contract_id'		        => $contract_id,
                'board'				        => $this->input->post('board'),
                'section'			        => $this->input->post('section'),
                'shift'				        => $this->input->post('shift'),
                'study_type'		        => $this->input->post('study_type'),
                'study_session'		        => $this->input->post('study_session'),
                'books_1'			        => $books_1,
                'books_2'			        => $books_2,
                'student_card'		        => $student_card,
                'password'			        => $password,
                'notes'				        => json_encode($this->input->post('note')),
                'last_edit'			        => $this->input->post('last_edit'),
                'reference_user_id'         => $this->input->post('reference_user_id')
            );

            $current_record = $this->db->get_where('students',array('student_id'=>$id))->result_array();

            if(
                $current_record[0]['cnic']!=$data['cnic'] || 
                $current_record[0]['class_id']!=$data['class_id'] || 
                $current_record[0]['first_name']!=$data['first_name'] ||
                $current_record[0]['last_name']!=$data['last_name'] ||
                $current_record[0]['contractor_id']!=$data['contractor_id'] ||
                $current_record[0]['contract_id']!=$data['contract_id']
            )
            {
                $this->student->updateStudent($data);

                $this->session->set_flashdata('message', 'Student update request submitted successfully!');
                redirect('students/edit_student/'.$id);
            }
            else
            {
                foreach(@$data as $k=>$value){
                    $this->db->set(''.$k.'', $value);
                }
                $this->db->where('student_id', $this->uri->segment(3));
                $this->db->update('students');

                $this->session->set_flashdata('message', 'Student updated successfully!');
                redirect('students/edit_student/'.$id);
            }
        }
        else
        {
            if($check_class[0]['dead_line_entry']>=date('Y-m-d'))
            {
                $check_request = $this->db->get_where('update_student_requests',array('student_id'=>$id, 'ok_by_admin'=>0))->result_array();
                if(count($check_request)>0)
                {
                    $this->session->set_flashdata('error', 'This Student update Request is already exist. Please contact Control Center to clear previous request.');
                    redirect('students/edit_student/'.$id);
                }

                if(count($this->input->post())==0)
                {
                    redirect('students/all_students');
                }
                if(@$this->input->post('books_1'))
                {
                    $books_1 = $this->input->post('books_1');
                }
                else
                {
                    $books_1 = '0';
                }
                if(@$this->input->post('books_2'))
                {
                    $books_2 = $this->input->post('books_2');
                }
                else
                {
                    $books_2 = '0';
                }
                if(@$this->input->post('student_card'))
                {
                    $student_card = $this->input->post('student_card');
                }
                else
                {
                    $student_card = '0';
                }

                if($this->input->post('password'))
                {
                    $password = md5($this->input->post('password'));
                }
                else
                {
                    $password = $this->input->post('old_password');
                }

                if($this->input->post('contractor_id')==0 || $this->input->post('contract_id')==0)
                {
                    $contractor_id = 0;
                    $contract_id = 0;
                }
                else
                {
                    $contractor_id = $this->input->post('contractor_id');
                    $contract_id = $this->input->post('contract_id');
                }
                
                $course = $this->db->get_where("courses","course_id = '".$this->input->post('course_id')."'")->row();
                $class = $this->db->get_where("classes","class_id = '".$this->input->post('class_id')."'")->row();
                $campus = $this->db->get_where("campuses","campus_id = '".$class->campus_id."'")->row();
                $student_count = $this->db->select('max(count) as count')->get_where("students","class_id = '".$this->input->post('class_id')."'")->row();
                if ($student_count == null)
                {
                    $student_count = 1;
                }
                else
                {
                    $student_count=$student_count->count+1;
                }
                    
                $roll_no = $student_count.'-'.$class->badge_no.'-'.$course->course_code.'-'.$campus->roll_no_code;

                $data = array(
                    'course_id'			        => $this->input->post('course_id'),
                    'study_campus'		        => $this->input->post('study_campus'),
                    'first_name'		        => $this->input->post('first_name'),
                    'last_name'			        => $this->input->post('last_name'),
                    'father_name'		        => $this->input->post('father_name'),
                    'gender'			        => $this->input->post('gender'),
                    'caste'				        => $this->input->post('caste'),
                    'religion'			        => $this->input->post('religion'),
                    'qualification'		        => $this->input->post('qualification'),
                    'class_id'			        => $this->input->post('class_id'),
                    'email'				        => $this->input->post('email'),
                    'cnic'				        => $this->input->post('cnic'),
                    'roll_no'			        => $roll_no,
                    'date_of_birth'		        => $this->input->post('date_of_birth'),
                    'district'		            => $this->input->post('district'),
                    'tehsil'		            => $this->input->post('tehsil'),
                    'mark_of_identification'	=> $this->input->post('mark_of_identification'),
                    'place_of_birth'		    => $this->input->post('place_of_birth'),
                    'registration_date'	        => $this->input->post('registration_date'),
                    'total_fee'			        => $this->input->post('total_fee'),
                    'blood_group'		        => $this->input->post('blood_group'),
                    'city'				        => $this->input->post('city'),
                    'address'			        => $this->input->post('address'),
                    'mobile'			        => $this->input->post('mobile'),
                    'emergency_no'		        => $this->input->post('emergency_no'),
                    'status' 			        => $this->input->post('status'),
                    'contractor_id'		        => $contractor_id,
                    'contract_id'		        => $contract_id,
                    'board'				        => $this->input->post('board'),
                    'section'			        => $this->input->post('section'),
                    'shift'				        => $this->input->post('shift'),
                    'study_type'		        => $this->input->post('study_type'),
                    'study_session'		        => $this->input->post('study_session'),
                    'books_1'			        => $books_1,
                    'books_2'			        => $books_2,
                    'student_card'		        => $student_card,
                    'password'			        => $password,
                    'notes'				        => json_encode($this->input->post('note')),
                    'last_edit'			        => $this->input->post('last_edit'),
                    'reference_user_id'         => $this->input->post('reference_user_id')
                );

                $current_record = $this->db->get_where('students',array('student_id'=>$id))->result_array();

                if(
                    $current_record[0]['cnic']!=$data['cnic'] || 
                    $current_record[0]['class_id']!=$data['class_id'] || 
                    $current_record[0]['first_name']!=$data['first_name'] ||
                    $current_record[0]['last_name']!=$data['last_name'] ||
                    $current_record[0]['contractor_id']!=$data['contractor_id'] ||
                    $current_record[0]['contract_id']!=$data['contract_id']
                )
                {
                    $this->student->updateStudent($data);

                    $this->session->set_flashdata('message', 'Student update request submitted successfully!');
                    redirect('students/edit_student/'.$id);
                }
                else
                {
                    foreach(@$data as $k=>$value){
                        $this->db->set(''.$k.'', $value);
                    }
                    $this->db->where('student_id', $this->uri->segment(3));
                    $this->db->update('students');

                    $this->session->set_flashdata('message', 'Student updated successfully!');
                    redirect('students/edit_student/'.$id);
                }
            }
            else
            {
                $this->session->set_flashdata('error', 'Failed to Update Student! Kindly check the Dead Line of this class.');
                redirect('students/edit_student/'.$id);
            }
        }
    }

    public function delete()
    {
        $student_id = $this->input->post('student_id');

        $check_request = $this->db->get_where('update_student_requests',array('student_id'=>$student_id, 'ok_by_admin'=>0))->result_array();
        if(count($check_request)>0)
        {
            $this->session->set_flashdata('error', 'This Student update Request is already exist. Please contact Control Center to clear previous request.');
            redirect('students/all_students');
        }

        $student=$this->db->get_where('students', array('student_id'=>$student_id))->result_array();

        $data = array(
            'course_id'			=> $student[0]['course_id'],
            'first_name'		=> $student[0]['first_name'],
            'last_name'			=> $student[0]['last_name'],
            'father_name'		=> $student[0]['father_name'],
            'gender'			=> $student[0]['gender'],
            'class_id'			=> $student[0]['class_id'],
            'roll_no'			=> $student[0]['roll_no'],
            'email'				=> $student[0]['email'],
            'cnic'				=> $student[0]['cnic'],
            'date_of_birth'		=> $student[0]['date_of_birth'],
            'registration_date'	=> $student[0]['registration_date'],
            'total_fee'			=> $student[0]['total_fee'],
            'blood_group'		=> $student[0]['blood_group'],
            'city'				=> $student[0]['city'],
            'address'			=> $student[0]['address'],
            'mobile'			=> $student[0]['mobile'],
            'emergency_no'		=> $student[0]['emergency_no'],
            'status' 			=> $student[0]['status'],
            'contractor_id'		=> $student[0]['contractor_id'],
            'board'				=> $student[0]['board'],
            'section'			=> $student[0]['section'],
            'shift'				=> $student[0]['shift'],
            'study_type'		=> $student[0]['study_type'],
            'books_1'			=> $student[0]['books_1'],
            'books_2'			=> $student[0]['books_2'],
            'student_card'		=> $student[0]['student_card'],
            'password'			=> $student[0]['password'],
            'notes'				=> $student[0]['notes'],
            'last_edit'			=> $student[0]['last_edit'],
            'status'			=> 0
        );
        $this->student->updateDeleteStudent($data, $student_id);


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

        $this->db->set('delete_type',$this->input->post('delete_type'));
        $this->db->set('student_id',$this->input->post('student_id'));
        $this->db->set('date',date('Y-m-d H:i:s'));
        $this->db->set('deleted_by',$this->session->userdata('name'));
        $this->db->set('reason',$this->input->post('reason'));
        $this->db->set('reason_detail',$this->input->post('reason_detail'));
        if(@$this->input->post('refund_amount'))
        {
            $this->db->set('refund_amount',$this->input->post('refund_amount'));
        }
        $this->db->set('image',$image);
        $this->db->insert('deleted_students');

        $this->session->set_flashdata('message', 'Student delete request submitted successfully!');
        redirect('students/all_students');
    }

    public function index()
    {

    }

    public function add_student($student = '')
    {
        if (@$this->input->post('cnic')){
            $student = $this->db->get_where("students","cnic = '".$this->input->post('cnic')."'")->row();
            $student = json_decode(json_encode($student), true);
        }
        $data['students'] = $student;
        $data['courses'] = $this->student->getCourses();
        $data['stud_types'] = $this->db->get("study_type")->result_array();
        $data['classes'] = $this->clas->getClasses();
        $data['campuses'] = $this->clas->getCampuses();
        $data['contractors'] = $this->contractor->getContractors();

        $data['references'] = $this->db->get_where('reference_users',array('status'=>1))->result_array();

        $data['occupations'] = $this->db->get_where('occupations',array('sub_of'=>0))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/add_student', $data);
        $this->load->view('inc/footer');
    }

    public function edit_student($id)
    {


        $data['courses'] = $this->student->getCourses();
        $data['student'] = $this->student->editStudent($id);
        $data['campuses'] = $this->clas->getCampuses();
        $data['contractors'] = $this->contractor->getContractors();

        if($data['student'][0]['contractor_id']!=0)
        {
            $data['contracts'] = $this->db->get_where('contracts',array('contractor_id'=>$data['student'][0]['contractor_id']))->result_array();
        }
        else
        {
            $data['contracts'] = array();
        }


        $access = checkUserAccess();
        $class_ids = @explode(',',$access[0]['class_ids']);

        $this->db->select('classes.*, campuses.campus_id,campuses.campus_id,campuses.campus_name,courses.course_name');
        $this->db->from('classes');
        $this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'left');
        $this->db->join('courses', 'courses.course_id=classes.course_id', 'left');
        $this->db->where(array('classes.status'=>'1'));

        if($this->session->userdata('role')!='Admin')
        {
            $this->db->where_in('classes.class_id', $class_ids);
        }
        $this->db->where('classes.campus_id', $data['student'][0]['campus_id']);
        $this->db->order_by('classes.class_id', 'asc');

        $data['classes'] = $this->db->get()->result_array();
        $data['study_types'] = $this->db->get_where("study_type","course_id = ".$data['student'][0]['course_id'])->result_array();
        $data['shift_types'] = $this->db->get_where("shifts","study_type_id = ".$data['student'][0]['study_type'])->result_array();

        $data['course_sessions'] = $this->db->get_where('course_sessions',array('course_id'=>$data['student'][0]['course_id']))->result_array();

        $data['references'] = $this->db->get_where('reference_users',array('status'=>1))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/edit_student', $data);
        $this->load->view('inc/footer');
    }

    public function all_students()
    {
        $data['campuses'] = $this->clas->getCampuses();
        $data['courses'] = '';
        if(@$this->input->post('form_submit')==1)
        {
            if(@$this->input->post('form_submit')==1 && @$this->input->post('type')=='attendance')
            {
                redirect(site_url().'/students/check_attendance/'.$this->input->post('class_id'));
            }
            if(@$this->input->post('form_submit')==1 && @$this->input->post('type')=='archived')
            {
                $campus_id = $this->input->post('campus_id');
                $class_id = $this->input->post('class_id');
                $data['class_id'] = $class_id;
                $data['campus_id'] = $campus_id;

                $this->db->select('students.*, classes.name as class_name,classes.admission_fee as freeze_fee, courses.course_name,campuses.campus_name');
                $this->db->from('students');
                $this->db->join('classes', 'classes.class_id=students.class_id', 'left');
                $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
                $this->db->join('courses', 'courses.course_id=students.course_id', 'left');
                $this->db->where(array('students.status'=>'0'));
                if(@$this->input->post('class_id'))
                {
                    $this->db->where(array('students.class_id'=> $class_id));
                }else{
                    $this->db->where(array('campuses.campus_id'=> $this->input->post('campus_id')));
                }
                $data['students'] = $this->db->get()->result_array();

            }
            elseif(@$this->input->post('form_submit')==1 && @$this->input->post('type')=='using_app')
            {
                $campus_id = $this->input->post('campus_id');
                $class_id = $this->input->post('class_id');
                $data['class_id'] = $class_id;
                $data['campus_id'] = $campus_id;

                $this->db->select('students.*, classes.name as class_name,classes.admission_fee as freeze_fee, courses.course_name,campuses.campus_name');
                $this->db->from('students');
                $this->db->join('classes', 'classes.class_id=students.class_id', 'left');
                $this->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'inner');
                $this->db->join('courses', 'courses.course_id = students.course_id', 'left');
                $this->db->where(array('students.status'=>'1'));
                if(@$this->input->post('class_id'))
                {
                    $this->db->where(array('students.class_id'=> $class_id));
                }else{
                    $this->db->where(array('campuses.campus_id'=> $this->input->post('campus_id')));
                }
                $data['students'] = $this->db->get()->result_array();

            }
            else
            {

                if (@$this->input->post("search_type") == "councilwise_roll_no")
                {
                    $this->db->select('campuses.campus_id, campuses.campus_name, punjab_council_roll_number.*, students.class_id, classes.name as class_name,classes.session as session, students.mobile, students.emergency_no, contractors.name as contractor_name,students.*,courses.*');
                    $this->db->from('punjab_council_roll_number');
                    $this->db->join('students', 'students.cnic=punjab_council_roll_number.cnic', 'left');
                    $this->db->join('classes', 'students.class_id=classes.class_id', 'left');
                    $this->db->join('courses', 'courses.course_id=classes.course_id', 'left');
                    $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
                    $this->db->join('contracts', 'students.contract_id=contracts.contract_id', 'left');
                    $this->db->join('contractors', 'contractors.contractor_id=contracts.contractor_id', 'left');
                    $this->db->where('punjab_council_roll_number.council_exam_no', @$this->input->post("council_exam_no"));
                    $this->db->where('punjab_council_roll_number.class', @$this->input->post("class"));
                    $this->db->where('campuses.campus_id', @$this->input->post("campus_id"));
                    $data['roll_numbers'] = $this->db->get()->result_array();
                }
                else{
                    $data['students'] = $this->student->getStudents();
                    $this->db->select('roll_no,MIN(students.registration_date) AS startdate,  MAX(dead_line) AS enddate');
                    $this->db->from('students');
                    $this->db->join('payments', 'payments.student_id = students.student_id', 'left');
                    $this->db->where(array('students.status'=>'1'));
                    if(@$this->input->post('class_id'))
                    {
                        $this->db->where('students.class_id', $this->input->post('class_id'));
                        $this->db->where("payments.payment_plan not like ('%consulation fee%')");
                    }

                    if(@$this->input->post('form_submit')==1 && @$this->input->post('type')=='councel_list')
                    {
                        $campus_id = $this->input->post('campus_id');
                        $class_id = $this->input->post('class_id');
                        $data['class_id'] = $class_id;
                        $data['campus_id'] = $campus_id;

                        if( $class_id != ''){

                            $data['result'] = $this->council->getClassStudents($class_id);

                        }
                    }

                    $this->db->order_by('students.roll_no', 'ASC');
                    $query = $this->db->get()->result_array();
                    $data['startdate']=$query[0]['startdate'];
                    $data['enddate']=$query[0]['enddate'];
                }
            }

            $data['sessions'] = $this->db->get_where('classes','status = 1 and course_id = "'.$this->input->post('course_id').'" and campus_id = "'.$this->input->post('campus_id').'"')->result_array();
            $data['campus_id']=$this->input->post('campus_id');
            $data['class_id']=$this->input->post('class_id');

        }
        

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/all_students', $data);
        $this->load->view('inc/footer');
    }

    public function print_selected_student_councel_list(){

        $student_ids = $this->input->post('student_ids');

        // $student_ids = explode(',',$student_ids);

        $campus_id = $this->input->post('campus_id');
        $class_id = $this->input->post('class_id');

        $this->db->select('*');
        $this->db->from('campuses');
        $this->db->where('campus_id', $campus_id);
        $data['campus'] = $this->db->get()->result_array();

        $this->db->select('classes.name , classes.exam_no ');
        $this->db->from('classes');
        $this->db->where('class_id', $class_id);
        $data['classess'] = $this->db->get()->result_array();

        // $data['result'] = $this->council->getClassStudents($class_id);

        $qry = 'SELECT student_id ,gender, first_name, last_name, father_name, roll_no, cnic, CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile, board, 03158042977 as institute FROM students WHERE  student_id IN ('.$student_ids.')  ORDER BY roll_no  ASC';

        // echo $qry;
        $data['result'] = $this->db->query($qry)->result_array();

//        print_r($data['result']);
//        exit();


        $this->load->view('council_list/get_print_of_concel_list', $data);


    }

    public function print_selected_student_councel_document(){

        $student_ids = $this->input->post('student_ids');
        $data['student_ids'] = explode(',',$student_ids);

//        print_r($data['student_ids']);
//        exit();


        $this->load->view('students/student_councel_doc', $data);
    }

    public function print_all_student_councel_document($class_id)
    {

        $this->db->select('student_id');
        $this->db->from('students');
        $this->db->where('status', 1);
        $this->db->where('class_id', $class_id);
        $this->db->order_by('roll_no', 'ASC');
        $student_idS =  $this->db->get()->result_array();

        $student_id_array = array();
        foreach ($student_idS as $student_id)
        {

            array_push($student_id_array, $student_id['student_id']);

        }

        $data['student_ids'] = $student_id_array;


        $this->load->view('students/student_councel_doc', $data);
    }

    public function selected_documents_with_type(){
        $student_ids = $this->input->post('student_ids');
        $select_doc_type = $this->input->post('select_doc_type');
        $to_type = '';
        $qty = $this->input->post('qty');

        if($select_doc_type == 'B-Form')
        {
            $to_type = 'B - Form';
        }else{
            $to_type=$select_doc_type;

        }


        $qry = 'SELECT student_id ,image, online_image, type  FROM student_documents WHERE  student_id IN ('.$student_ids.') AND  type like "%'.$to_type.'%"';
        $data['student_documents'] = $this->db->query($qry)->result_array();
        $data['type'] = $select_doc_type;
        $data['doc_qty'] = $qty;
        if($select_doc_type === 'admission')
        {


            redirect('documents/print_admission_form/'.$student_ids );

        }

        if($select_doc_type === 'council')
        {
            redirect('documents/print_council_admission_form/'.$student_ids );
        }

        if($select_doc_type === 'council_character_certificate')
        {
            redirect('documents/print_council_character_certificate/'.$student_ids );
        }

        if($select_doc_type === 'council_admission_letter')
        {
            redirect('documents/print_council_admission_letter/'.$student_ids );
        }


        $this->load->view('students/selected_documents_with_type', $data);
    }

    public function all_struckofstudent($check)
    {
        $this->db->select('students.*,campuses.*,courses.*, classes.name as class_name,ast.approval_by,ast.reason,ast.action_type,ast.status,ast.created_at,ast.created_by as createdby');
        $this->db->from('struckof_procedures ast');
        $this->db->join('students', 'ast.student_id=students.student_id', 'inner');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
        $this->db->join('courses', 'courses.course_id=students.course_id', 'left');
        $this->db->where('students.status',1);
       // $this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
        // if ($check == 0){
        //     $this->db->where("ast.status = 'pending'");
        // }elseif ($check == 1){
        //     $this->db->where('(ast.status = "pending" and ast.need_approval = 1)' );
        // }
        $this->db->where("ast.status = 'pending'");
        $this->db->group_by('students.student_id');
        $data['students'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/struckofstudent_list', $data);
        $this->load->view('inc/footer');
    }

    public function checkstudentstatus()
    {
        $access = checkUserAccess();
        $class_ids = @explode(',',$access[0]['class_ids']);

        $val = @$this->input->post('search');


        if(@$val!=''){

            $this->db->select('students.*, classes.name as class_name,machine_data.machine_id,campuses.campus_name');
            $this->db->from('students');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
            $this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
            //$this->db->like('students.roll_no', $val);
            //$this->db->like('students.cnic', $val);
            //$this->db->where(array('students.status'=>1));
            $this->db->where("(students.roll_no LIKE '%".$val."%' OR students.cnic LIKE '%".$val."%' OR students.mobile LIKE '%".$val."%' OR students.emergency_no LIKE '%".$val."%' OR students.first_name LIKE '%".$val."%' OR students.last_name LIKE '%".$val."%' OR students.father_name LIKE '%".$val."%')", NULL, FALSE);

            if($this->session->userdata('role')!='Admin'){
                $this->db->where_in('classes.class_id', $class_ids);
            }

            $data['students'] = $this->db->get()->result_array();

        }else{

            $data['students'] = null;

        }
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/struckofbook_students', $data);
        $this->load->view('inc/footer');
    }

    public function struckofstudentview($studentid)
    {
        $access = checkUserAccess();
        $class_ids = @explode(',',$access[0]['class_ids']);

        if($studentid!=''){

            $this->db->select('students.*,campuses.campus_name, courses.course_name ,classes.name as class_name,machine_data.machine_id');
            $this->db->from('students');            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
            $this->db->join('courses', 'courses.course_id=students.course_id', 'left');
            $this->db->where("(students.student_id = '".$studentid."')", NULL, FALSE);

            if($this->session->userdata('role')!='Admin'){
                $this->db->where_in('classes.class_id', $class_ids);
            }
            $data['students'] = $this->db->get()->result_array();

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

//            $this->db->select('*');
//            $this->db->from('struckofdetails_students');
//            $this->db->where("(struckofdetails_students.student_id = '".$studentid."')", NULL, FALSE);

            $this->db->select('*');
            $this->db->from('struckof_procedures');
            $this->db->where("(student_id = '".$studentid."')", NULL, FALSE);

            $data['struckofdata'] = $this->db->get()->result_array();
            $data['studentid'] = $studentid;

        }else{
            $data['students'] = null;
        }
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/struckofstudent', $data);
        $this->load->view('inc/footer');
    }

    public function freezestudentview($studentid)
    {
        $access = checkUserAccess();
        $class_ids = @explode(',',$access[0]['class_ids']);

        if($studentid!=''){

            $this->db->select('students.*,campuses.campus_name, courses.course_name,classes.freeze_fee as freeze_fee ,classes.name as class_name,machine_data.machine_id');
            $this->db->from('students');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
            $this->db->join('courses', 'courses.course_id=students.course_id', 'left');

            $this->db->where("(students.student_id = '".$studentid."')", NULL, FALSE);

            if($this->session->userdata('role')!='Admin'){
                $this->db->where_in('classes.class_id', $class_ids);
            }

            $data['students'] = $this->db->get()->result_array();

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



            $this->db->select('*');
            $this->db->from('freeze_student');
            $this->db->where("(freeze_student.student_id = '".$studentid."')", NULL, FALSE);

            $data['freezedata'] = $this->db->get()->result_array();
            $data['studentid'] = $studentid;
            $this->db->select('campuses.*');
            $this->db->from('campus_rules');
            $this->db->join('campuses','campuses.campus_id=campus_rules.campus_id','inner');
            $this->db->where('campus_rules.college_fee',1);
            $data['campuses'] = $this->db->get()->result_array();

        }else{

            $data['students'] = null;

        }
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/student_freeze', $data);
        $this->load->view('inc/footer');
    }

    public function addstruckofdetails($studentid,$index)
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
        $config['upload_path'] = 'recording/';

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

        $this->db->set('student_id', $studentid);
        $this->db->set('contact_from_no', $this->input->post('fromno'));
        $this->db->set('contact_to_no', $this->input->post('tono'));
        $this->db->set('action_type', $this->input->post('delete_type'));
        $this->db->set('reason', $this->input->post('reason'));
        $this->db->set('detail', $this->input->post('reason_detail'));
        $this->db->set('process_count', $this->input->post('process_id'));
        $this->db->set('created_by',$this->session->userdata('user_id'));
        $this->db->set('status',"0");
        $this->db->set('proof_image',$image);
        $this->db->set('post_receipt',$post_image);
        $this->db->set('whatsapp_image',$whatsapp_image);
        $this->db->set('sms_image',$sms_image);
        $this->db->set('recording',$recording);
        $this->db->insert('struckofdetails_students');

        if (($this->input->post('action_type') == 'process' && $index == 2) || ($this->input->post('action_type') == 'immediate' && $index == 0) ){
            $this->db->set("need_approval", "1");
            $this->db->where ("student_id = '".$studentid."' and process_count = ".$this->input->post('process_id'));
            $this->db->update("struckof_procedures");
        }

        if($this->input->post('amount') != ''){
            $std_st = $this->db->join('classes','classes.class_id = students.class_id')->get_where('students','student_id = "'.$studentid.'"')->row();
            $this->db->set('title',"Expense For Struck of Letter No ".($index+1));
            $this->db->set('date',$this->input->post('from_date'));
            $this->db->set('amount',$this->input->post('amount'));
            $this->db->set('purpose',"Expense for Struck of Letter post to $std_st->first_name $std_st->last_name - $std_st->roll_no - $std_st->mobile");
            $this->db->set('actual_date', date('Y-m-d H:i:s'));
            $this->db->set('image', $post_image);
            $this->db->set('expense_category_id', '32');
            $this->db->set('add_by_id', $this->session->userdata('user_id'));
            $this->db->set('campus_id', $std_st->campus_id);
            $this->db->set('approved_status', 1);
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->insert('expenses');


            $this->db->set('remaining_amount', 'remaining_amount -' . $this->input->post('amount') . '', false);
            $this->db->where('assign_to', $this->session->userdata('user_id'));
            $this->db->update('petty_cash_college_wise');
        }

        redirect('students/struckofstudentview/'.$studentid);
    }

    public function rejectstruckofstudent($student_id,$process_id)
    {
        $this->db->set   ('status','reject');
        $this->db->set   ('approval_by',$this->session->userdata('name'));
        $this->db->where ("student_id = '".$student_id."' and process_count = $process_id");
        $this->db->update('struckof_procedures');


        $this->db->set('status','2');
        $this->db->set('updated_by',$this->session->userdata('name'));
        $this->db->where("student_id = '".$student_id."' and process_count = $process_id");
        $this->db->update('struckofdetails_students');

        $this->session->set_flashdata('message', 'Student Rejected request successfully!');
        redirect('students/struckofstudentview/'.$student_id);
    }

    public function addfreezedetails($studentid)
    {
        $image = '';
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
            $data = array('msg' => $this->upload->display_errors());
            $image = '';
        }
        else {
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = $data['upload_data']['file_name'];
            }
        }

        $dead_line = date('Y-m-d');
        $challan_no = $this->getChallanNo();

        $this->db->set('amount',  $this->input->post('fee'));
        $this->db->set('actual_amount',  $this->input->post('fee'));
        $this->db->set('dead_line', $dead_line);
        $this->db->set('student_id', $studentid);
        $this->db->set('payment_plan', 'Custom Plan');
        $this->db->set('payment_comment', 'Freeze Payment Fee');
        $this->db->set('challan_no', $challan_no);
        $this->db->set('paid', '1');
        $this->db->set('paid_date', date('Y-m-d'));
        $this->db->set('actual_paid_date', date('Y-m-d'));
        $this->db->set('fee_pay_through', 'college');
        $this->db->set('fee_submit_type', 'computer_challan');
        $this->db->set('submitted_fee_campus_id', $this->input->post('campus_id'));
        $this->db->set('add_by', $this->session->userdata('name'));
        $this->db->set('last_edit', $this->session->userdata('name'));
        $this->db->insert('payments');
        $insert_id = $this->db->insert_id();

        $this->db->set('student_id', $studentid);
        $this->db->set('reason', $this->input->post('reason'));
        $this->db->set('fee_amount', $this->input->post('fee'));
        $this->db->set('rejoin_date', $this->input->post('from_date'));
        $this->db->set('image_proof',$image);
        $this->db->set('challan_id',$insert_id);
        $this->db->set('created_by',$this->session->userdata('name'));
        $this->db->insert('freeze_student');
        $student_id=$studentid;

        $check_request = $this->db->get_where('update_student_requests',array('student_id'=>$student_id, 'ok_by_admin'=>0))->result_array();
        if(count($check_request)>0)
        {
            $this->session->set_flashdata('error', 'This Student update Request is already exist. Please contact Control Center to clear previous request.');
            redirect('students/all_students');
        }
        
        $student=$this->db->get_where('students', array('student_id'=>$student_id))->result_array();

        $data = array(
            'course_id'			=> $student[0]['course_id'],
            'first_name'		=> $student[0]['first_name'],
            'last_name'			=> $student[0]['last_name'],
            'father_name'		=> $student[0]['father_name'],
            'gender'			=> $student[0]['gender'],
            'class_id'			=> $student[0]['class_id'],
            'roll_no'			=> $student[0]['roll_no'],
            'email'				=> $student[0]['email'],
            'cnic'				=> $student[0]['cnic'],
            'date_of_birth'		=> $student[0]['date_of_birth'],
            'registration_date'	=> $student[0]['registration_date'],
            'total_fee'			=> $student[0]['total_fee'],
            'blood_group'		=> $student[0]['blood_group'],
            'city'				=> $student[0]['city'],
            'address'			=> $student[0]['address'],
            'mobile'			=> $student[0]['mobile'],
            'emergency_no'		=> $student[0]['emergency_no'],
            'status' 			=> $student[0]['status'],
            'contractor_id'		=> $student[0]['contractor_id'],
            'board'				=> $student[0]['board'],
            'section'			=> $student[0]['section'],
            'shift'				=> $student[0]['shift'],
            'study_type'		=> $student[0]['study_type'],
            'books_1'			=> $student[0]['books_1'],
            'books_2'			=> $student[0]['books_2'],
            'student_card'		=> $student[0]['student_card'],
            'password'			=> $student[0]['password'],
            'notes'				=> $student[0]['notes'],
            'last_edit'			=> $student[0]['last_edit'],
            'status'			=> 0
        );
        $this->student->updateDeleteStudent($data, $student_id);
        $image = '';

        $this->db->set('delete_type','Freeze');
        $this->db->set('student_id',$student_id);
        $this->db->set('date',date('Y-m-d H:i:s'));
        $this->db->set('deleted_by',$this->session->userdata('name'));
        $this->db->set('reason',$this->input->post('reason'));
        $this->db->set('reason_detail','Freez Paid Amount : '.$this->input->post('fee'));

        $this->db->set('image',$image);
        $this->db->insert('deleted_students');


        redirect('students/freezestudentview/'.$studentid);
    }

    public function addrevivedetails()
    {
        $image = '';
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

        $dead_line = date('Y-m-d');
        $challan_no = $this->getChallanNo();
        $student_id = $this->input->post('student_id');

        $counts = $this->db->get_where("payments","student_id = $student_id and paid = 1")->result_array();
        if ($counts > 0) {
            $this->db->set('amount', $this->input->post('fee'));
            $this->db->set('actual_amount', $this->input->post('fee'));
            $this->db->set('dead_line', $dead_line);
            $this->db->set('student_id', $this->input->post('student_id'));
            $this->db->set('payment_plan', 'Custom Plan');
            $this->db->set('payment_comment', 'Re-Admission Fee');
            $this->db->set('challan_no', $challan_no);
            $this->db->set('paid', '1');
            $this->db->set('paid_date', date('Y-m-d'));
            $this->db->set('actual_paid_date', date('Y-m-d'));
            $this->db->set('fee_pay_through', 'college');
            $this->db->set('fee_submit_type', 'computer_challan');
            $this->db->set('submitted_fee_campus_id', $this->input->post('campus_id'));
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->set('last_edit', $this->session->userdata('name'));
            $this->db->insert('payments');

            $this->db->set("status", 1);
            $this->db->where("student_id", $this->input->post('student_id'));
            $this->db->update("students");
        }else{
            $this->db->where("student_id", $this->input->post('student_id'));
            $this->db->delete("payments");

            $this->db->set("status", 1);
            $this->db->where("student_id", $this->input->post('student_id'));
            $this->db->update("students");
        }

//        $this->db->set('student_id', $this->input->post('student_id'));
//        $this->db->set('reason', $this->input->post('reason'));
//        $this->db->set('fee_amount', $this->input->post('fee'));
//        $this->db->set('image_proof',$image);
//        $this->db->set('challan_id',$insert_id);
//        $this->db->set('type','delete');
//        $this->db->set('created_by',$this->session->userdata('name'));
//        $this->db->insert('freeze_student');
//        $student=$this->db->get_where('students', array('student_id'=>$this->input->post('student_id')))->result_array();
//
//        $data = array(
//            'course_id'			=> $student[0]['course_id'],
//            'first_name'		=> $student[0]['first_name'],
//            'last_name'			=> $student[0]['last_name'],
//            'father_name'		=> $student[0]['father_name'],
//            'gender'			=> $student[0]['gender'],
//            'class_id'			=> $student[0]['class_id'],
//            'roll_no'			=> $student[0]['roll_no'],
//            'email'				=> $student[0]['email'],
//            'cnic'				=> $student[0]['cnic'],
//            'date_of_birth'		=> $student[0]['date_of_birth'],
//            'registration_date'	=> $student[0]['registration_date'],
//            'total_fee'			=> $student[0]['total_fee'],
//            'blood_group'		=> $student[0]['blood_group'],
//            'city'				=> $student[0]['city'],
//            'address'			=> $student[0]['address'],
//            'mobile'			=> $student[0]['mobile'],
//            'emergency_no'		=> $student[0]['emergency_no'],
//            'status' 			=> $student[0]['status'],
//            'contractor_id'		=> $student[0]['contractor_id'],
//            'board'				=> $student[0]['board'],
//            'section'			=> $student[0]['section'],
//            'shift'				=> $student[0]['shift'],
//            'study_type'		=> $student[0]['study_type'],
//            'books_1'			=> $student[0]['books_1'],
//            'books_2'			=> $student[0]['books_2'],
//            'student_card'		=> $student[0]['student_card'],
//            'password'			=> $student[0]['password'],
//            'notes'				=> $student[0]['notes'],
//            'last_edit'			=> $student[0]['last_edit'],
//            'status'			=> 1
//        );
//        $this->student->updateDeleteStudent($data,$this->input->post('student_id'));
        redirect('students/payments_paid/'.$this->input->post('student_id'));
    }

    public function addrevivedetails_new()
    {
        $reason = $this->input->post('reason');
        $student_id = $this->input->post('student_id');
        $class_id = $this->input->post('class_id');

        $student=$this->db->get_where('students', array('student_id'=>$student_id))->result_array();
        $payments=$this->db->get_where('payments', array('student_id'=>$student_id))->result_array();
        $archive_payment=$this->db->select("Max(payment_id) as payment_id")->get_where('archive_payments', array('student_id'=>$student_id))->result_array();
        if (count($archive_payment) > 0)
        {
            $archive_payment = $archive_payment[0]['payment_id'];
            $archive_payment++;
        }
        else
        {
            $archive_payment = 1;
        }

        foreach ($payments as $payment) {
            foreach (@$payment as $k => $value) {
                $this->db->set('' . $k . '', $value);
            }
            $this->db->set('student_id', $student_id);
            $this->db->set('payment_id', $archive_payment);
            $this->db->set('reason', $reason);
            $this->db->insert('archive_payments');
        }
        $this->db->delete('payments', array('student_id' => $student_id));

        $data = array(
            'class_id'			=> $class_id,
            'status'			=> 1
        );

        $this->store_Student($data,$student_id);
        redirect('students/payments/'.$student_id.'/true');

    }

    public function struckofstudent($student_id,$process_id)
    {
        $student=$this->db->get_where('students', array('student_id'=>$student_id))->result_array();

        $data = array(
            'course_id'			=> $student[0]['course_id'],
            'first_name'		=> $student[0]['first_name'],
            'last_name'			=> $student[0]['last_name'],
            'father_name'		=> $student[0]['father_name'],
            'gender'			=> $student[0]['gender'],
            'class_id'			=> $student[0]['class_id'],
            'roll_no'			=> $student[0]['roll_no'],
            'email'				=> $student[0]['email'],
            'cnic'				=> $student[0]['cnic'],
            'date_of_birth'		=> $student[0]['date_of_birth'],
            'registration_date'	=> $student[0]['registration_date'],
            'total_fee'			=> $student[0]['total_fee'],
            'blood_group'		=> $student[0]['blood_group'],
            'city'				=> $student[0]['city'],
            'address'			=> $student[0]['address'],
            'mobile'			=> $student[0]['mobile'],
            'emergency_no'		=> $student[0]['emergency_no'],
            'status' 			=> $student[0]['status'],
            'contractor_id'		=> $student[0]['contractor_id'],
            'board'				=> $student[0]['board'],
            'section'			=> $student[0]['section'],
            'shift'				=> $student[0]['shift'],
            'study_type'		=> $student[0]['study_type'],
            'books_1'			=> $student[0]['books_1'],
            'books_2'			=> $student[0]['books_2'],
            'student_card'		=> $student[0]['student_card'],
            'password'			=> $student[0]['password'],
            'notes'				=> $student[0]['notes'],
            'last_edit'			=> $student[0]['last_edit'],
            'status'			=> 0
        );
        $this->student->updateDeleteStudent($data, $student_id);

        $this->db->set('status','1');
        $this->db->set('updated_by',$this->session->userdata('name'));
        $this->db->where("(struckofdetails_students.student_id = '".$student_id." and process_count = $process_id')", NULL, FALSE);
        $this->db->update('struckofdetails_students');

        $this->db->set   ('status','complete');
        $this->db->set   ('approval_by',$this->session->userdata('name'));
        $this->db->where ("student_id = '".$student_id."' and process_count = $process_id");
        $this->db->update('struckof_procedures');

        $this->db->select('*');
        $this->db->from('struckofdetails_students');
        $this->db->where("(struckofdetails_students.student_id = '".$student_id."  and process_count = $process_id')", NULL, FALSE);
        $resultstruckdetails = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('struckof_procedures');
        $this->db->where ("student_id = '".$student_id."' and process_count = $process_id");
        $procedures = $this->db->get()->result_array();

        $this->db->set('delete_type','Struck Of');
        $this->db->set('student_id',$student_id);
        $this->db->set('date',date('Y-m-d H:i:s'));
        $this->db->set('deleted_by',$this->session->userdata('name'));
        $this->db->set('reason', $procedures[0]['reason']);
        $this->db->set('reason_detail', $procedures[0]['reason']);
        if ($procedures[0]['action_type'] == "immediate"){
            if (@$this->input->post('refund_amount')) {
                $this->db->set('refund_amount', $this->input->post('refund_amount'));
            }
            $this->db->set('image', $resultstruckdetails[0]['proof_image']);
        }
        else {
            $this->db->set('image', $resultstruckdetails[2]['proof_image']);
        }
        $this->db->set('status','1');
        $this->db->set('approve_by',$this->session->userdata('name'));
        $this->db->insert('deleted_students');

        $this->db->set('status','0');
        $this->db->where("(students.student_id = '".$student_id."')",NULL,FALSE);
        $this->db->update('students');

        if (@$resultstruckdetails[0]['refund_amount'] != 0) {
            //ADD REFUND AMOUNT IN EXPENSES
            $this->db->set('date', date('Y-m-d'));
            $this->db->set('actual_date', date('Y-m-d'));
            $this->db->set('purpose', 'Refund issue and approved by '.$this->session->userdata('name').'');
            $this->db->set('title', 'Student Refund');
            $this->db->set('amount', $resultstruckdetails[0]['refund_amount']);
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->set('last_edit', $this->session->userdata('name'));
            $this->db->set('campus_id', $resultstruckdetails[0]['campus_id']);
            $this->db->set('expense_category_id', 7);
            $this->db->insert('expenses');
        }
        if (@$resultstruckdetails[1]['refund_amount'] != 0) {
            //ADD REFUND AMOUNT IN EXPENSES
            $this->db->set('date', date('Y-m-d'));
            $this->db->set('actual_date', date('Y-m-d'));
            $this->db->set('purpose', 'Refund issue and approved by '.$this->session->userdata('name').'');
            $this->db->set('title', 'Student Refund');
            $this->db->set('amount', $resultstruckdetails[1]['refund_amount']);
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->set('last_edit', $this->session->userdata('name'));
            $this->db->set('campus_id', $resultstruckdetails[0]['campus_id']);
            $this->db->set('expense_category_id', 7);
            $this->db->insert('expenses');
        }

        $this->session->set_flashdata('message', 'Student delete request submitted successfully!');
        redirect('students/struckofstudentviewprocess/'.$student_id.'/'.$process_id);
    }

    public function all_struckofstudent_report()
    {

        $val = @$this->input->post('strucktype');


        if(@$val!='') {

            $from = @$this->input->post('from_date');
            $to = @$this->input->post('to_date');


            $this->db->select('students.*, classes.name as class_name,machine_data.machine_id,struckofdetails_students.updated_by,struckofdetails_students.reason,struckofdetails_students.status
            ,struckofdetails_students.created_at as created , users.first_name as inquiry');
            $this->db->from('struckofdetails_students');
            $this->db->join('students', 'struckofdetails_students.student_id=students.student_id', 'inner');
            $this->db->join('users', 'users.user_id = struckofdetails_students.created_by', 'inner');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
            $this->db->where("struckofdetails_students.status = '" . $val. "' 
                              and struckofdetails_students.created_at >= '".$from."'
                              and struckofdetails_students.created_at <= '".$to."'");
            $this->db->group_by('struckofdetails_students.student_id');

            $data['students'] = $this->db->get()->result_array();


            $this->load->view('inc/header');
            $this->load->view('inc/sidebar');
            $this->load->view('students/struckofstudent_report', $data);
            $this->load->view('inc/footer');

        }else{

            $this->load->view('inc/header');
            $this->load->view('inc/sidebar');
            $this->load->view('students/struckofstudent_report');
            $this->load->view('inc/footer');

        }

    }

    public function upload_documents($id)
    {
        $data['documents'] = $this->student->uploadedDocuments($id);
        $data['student'] = $this->student->editStudent($id);

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/upload_documents', $data);
        $this->load->view('inc/footer');
    }

    public function delete_documents($id, $photo_id)
    {
        $this->student->deleteDocument($photo_id);
        $this->session->set_flashdata('message', 'Image deleted successfully');
        redirect('students/upload_documents/'.$id);
    }

    public function upload($id)
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
        if (!$this->upload->do_upload('clock_image')) {
            $data = array('msg' => $this->upload->display_errors());
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





        $this->student->uploadDocument($id, $student_document, $type);
        $this->session->set_flashdata('message', 'Document Uploaded Successfully.');
        redirect('students/upload_documents/'.$id);
    }

    public function payments($id,$isupdate = false)
    {
        $plan_created = $this->student->payment_paid($id);
        $data['student'] = $this->student->getSingleStudent($id);
        $plan_count = $this->db->order_by('dead_line','ASC')->get_where('payments',"student_id = '$id' and paid = 1")->result_array();

        if(count($plan_created)>0)
        {
            if (count($plan_count) > 0)
                redirect('students/payments_paid/'.$id);
            else
            {
                $reg_form = $this->db->get_where("student_documents","student_id = '$id' and type='Rules and Regulation Form'")->result_array();
                if (count($reg_form) > 0){
                    redirect('students/payments_paid/'.$id);
                }else
                    redirect('students/admission_letter_print/'.$id);
            }
        }
        if (!$isupdate) {
            $data['plans'] = $this->db->get_where('fee_rules', array('course_id' => $data['student'][0]['course_id'],
                'session' => $data['student'][0]['session'],
                'last_date >=' => date('Y-m-d'),
                'status' => 'active'))->result_array();
        }else
        {
            $data['plans'] = $this->db->get_where('fee_rules', array('course_id' => $data['student'][0]['course_id'],
                'session' => $data['student'][0]['session']))->result_array();

        }
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/payments', $data);
        $this->load->view('inc/footer');
    }

    public function contractor_payments($contractor_id)
    {
        $plan_created = $this->student->contractor_payment_paid($contractor_id);
        $data['contractor'] = $this->student->getSingleContractor($contractor_id);
        if(count($plan_created)>0)
        {
            redirect('students/contractor_payments_paid/'.$contractor_id);
        }

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/contractor_payments', $data);
        $this->load->view('inc/footer');
    }

    public function add_payment_plan($id)
    {

        if(!$this->input->post('plan_id'))
        {
            $this->session->set_flashdata('error', 'Please select payment plan.');
            redirect('students/payments/'.$id);
        }
        
        $payment_plan = $this->input->post('plan_id');
        $instdate = $this->input->post('instdate');
        $extendinstallments = $this->input->post('installments');
        $this->db->trans_start();

        $plan = $this->db->get_where('fee_rules', array('fee_rule_id'=>$payment_plan))->result_array();
        $plan=$plan[0];
        
        $session = $this->db->get_where('course_sessions', array('session_name'=>$plan['session'],'course_id'=>$plan['course_id']))->row_array();
        $this->db->set('total_fee',$plan['total_fee']-$this->input->post('discount'));
        $this->db->set('plan_id',$payment_plan);
        $this->db->set('current_session_fee',$plan['total_fee']);
        $this->db->where('student_id',$id);
        $this->db->update('students');

        $studentDetails = $this->db->get_where('students',array('student_id'=>$id))->result_array();
        $class_id = $studentDetails[0]['class_id'];
        $classDeatils = $this->db->get_where('classes',array('class_id'=>$class_id))->result_array();
        $exam_no = $classDeatils[0]['exam_no'];
        // print_r($exam_no);
        // exit();
        
        if($session) {
            $exam_sequence_id = $this->db->get_where('exam_sequence',[
                'course_id' => $plan['course_id'],
                'first_year' => $session['first_council_exam_no'],
                'class' => 1
            ])->row_array();
            
            
            $exam_sequence_id = $exam_sequence_id['id'];
    
            $this->db->select('*');
            $this->db->from('council_sequence');
            $this->db->where('course_id', $plan['course_id']);
            $this->db->where('has_fee', 1);
            $this->db->where('action_type', 'fee');
            $this->db->where_in('recurring',['One Time','Each Exam','Every Semester']);
            $sequences = $this->db->get()->result_array();
            $today = date('Y-m-d');
            $errors = [];

            foreach($sequences as $sequence){
                $fee = $this->db
                    ->where('sequence_fee_id', $sequence['council_sequence_id'])
                    ->where('exam_sequence_id', $exam_sequence_id)
                    // ->where('from_date <=', $today)
                    ->where('to_date >=', $today)
                    ->order_by('from_date','ASC')
                    ->get('council_sequence_fee_rules')
                    ->row_array();
                if($fee){
                    $dead_line = $fee['from_date'];
                    $challan_no = $this->getChallanNo();
            
                    $this->db->set('amount', $fee['exam_fee']);
                    $this->db->set('disc_per_inst', $plan['disc_per_inst']);
                    $this->db->set('dead_line', $dead_line);
                    $this->db->set('student_id', $id);
                    $this->db->set('payment_plan', 'consulation fee');
                    $this->db->set('payment_comment', 'This fee for next exam # '.$exam_no.' 1st Year');
                    $this->db->set('challan_no', $challan_no);
                    $this->db->set('add_by',$this->session->userdata('name'));
                    $this->db->set('last_edit',$this->session->userdata('name'));
                    $this->db->set('exam_sequence_id', $exam_sequence_id);
                    $this->db->set('council_sequence_id', $sequence['council_sequence_id']);
            
                    $this->db->insert('payments');
            
                } else {
                    $errors[] = 'Please Generate Fee For '.$sequence['type_name'];
                }
            }
        }
        
        if(!empty($errors)){
            $this->db->trans_rollback();
        
            $this->session->set_userdata('error', implode('<br>', $errors));
        
            redirect('students/payments/'.$id);
        }
        

        if ($extendinstallments<$plan['no_of_installments']){
            $extendinstallments=$plan['no_of_installments'];
        }
        else{
            $extendinstallments;
        }

        
        
        // for($i=1; $i<=1; $i++)
        // {
        //     $dead_line = $plan['last_date_council_fee'];
        //     $challan_no = $this->getChallanNo();

        //     $this->db->set('amount', $plan['first_time_council_fee']);
        //     $this->db->set('disc_per_inst', $plan['disc_per_inst']);
        //     $this->db->set('dead_line', $dead_line);
        //     $this->db->set('student_id', $id);
        //     $this->db->set('payment_plan', 'consulation fee');
        //     $this->db->set('payment_comment', 'This fee for next exam # '.$exam_no.' no exam 1st Year'); //This fee for next exam # 24 no exam 1st Year
        //     $this->db->set('challan_no', $challan_no);
        //     $this->db->set('add_by',$this->session->userdata('name'));
        //     $this->db->set('last_edit',$this->session->userdata('name'));
        //     $this->db->insert('payments');
        // }

        $dead_line = date("Y-m-d");
        $challan_no = $this->getChallanNo();
        $this->db->set('amount', $plan['installment_on_admission']);
        $this->db->set('dead_line', $dead_line);
        $this->db->set('student_id', $id);
        $this->db->set('payment_plan', "Auto");
        $this->db->set('disc_per_inst', $plan['disc_per_inst']);
        $this->db->set('challan_no', $challan_no);
        $this->db->set('payment_comment', 'College Fee');
        $this->db->set('add_by',$this->session->userdata('name'));
        $this->db->set('last_edit',$this->session->userdata('name'));
        $this->db->insert('payments');


        $totalamount=$plan['total_fee']-$this->input->post('discount')-$plan['installment_on_admission'];
        $permonth = $totalamount/$extendinstallments;
        for($i=1; $i<=$extendinstallments; $i++)
        {
            $dead_line = date("Y-m-" . $instdate, strtotime('first day of +' . $i . ' month'));
            $challan_no = $this->getChallanNo();
            $this->db->set('amount', $permonth);
            $this->db->set('dead_line', $dead_line);
            $this->db->set('student_id', $id);
            $this->db->set('payment_plan', "Auto");
            $this->db->set('disc_per_inst', $plan['disc_per_inst']);
            $this->db->set('challan_no', $challan_no);
            $this->db->set('payment_comment', 'College Fee');
            $this->db->set('add_by',$this->session->userdata('name'));
            $this->db->set('last_edit',$this->session->userdata('name'));
            $this->db->insert('payments');
        }
        $this->db->trans_complete();

        redirect('students/payments/'.$id);
    }

    public function add_contractor_payment_plan($id)
    {
        $payment_plan = $this->input->post('payment_plan');

        if($payment_plan=='Custom Plan')
        {
            $total_installments = (count($this->input->post())-5)/2;

            for($i=1; $i<=$total_installments; $i++)
            {
                $dead_line = $this->input->post('dead_line_'.$i);
                $challan_no = $this->getChallanNo();

                $this->db->set('amount', $this->input->post('amount_'.$i));
                $this->db->set('dead_line', $dead_line);
                $this->db->set('contractor_id', $id);
                $this->db->set('payment_plan', $payment_plan);
                $this->db->set('challan_no', $challan_no);
                $this->db->set('add_by',$this->session->userdata('name'));
                $this->db->set('last_edit',$this->session->userdata('name'));
                $this->db->insert('payments');
            }

        }
        for($i=1; $i<=2; $i++)
        {
            $dead_line = $this->input->post('consulation_dead_line_'.$i);
            $challan_no = $this->getChallanNo();

            $this->db->set('amount', $this->input->post('consulation_fee_'.$i));
            $this->db->set('dead_line', $dead_line);
            $this->db->set('contractor_id', $id);
            $this->db->set('payment_plan', 'consulation fee');
            $this->db->set('challan_no', $challan_no);
            $this->db->set('add_by',$this->session->userdata('name'));
            $this->db->set('last_edit',$this->session->userdata('name'));
            $this->db->insert('payments');
        }
        redirect('students/contractor_payments_paid/'.$id);
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

    public function getPayproID()
    {
        $random_number = rand(1000, 999999999);
        $check_challan_no = $this->db->get_where('students_payments', array('order_number'=>$random_number))->result_array();
        if(count($check_challan_no)>0)   {
            $random_number = $this->getPayproID();
        }
        else {
            return $random_number;
        }
    }

    public function payments_paid($id)
    {
        $qry = "SELECT DISTINCT `merged_challan`, discount as discount FROM `payments` WHERE student_id=$id and merged_challan is not null GROUP by merged_challan UNION ALL SELECT DISTINCT `merged_challan`, sum(discount) as discount FROM `payments` WHERE student_id=$id and merged_challan is null and paid=1";
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

        $data['old_plans'] = $this->db->group_by('payment_id')->get_where('archive_payments',array('student_id'=>$id))->result_array();

        $data['payments'] = $this->student->payment_paid($id);
        $data['deleted_payments'] = $this->student->deleted_payment_paid($id);
        $data['getCountDeletedFess'] = $this->student->getCountDeletedFess($id);
        $data['getCountShiftedFess'] = $this->student->getCountShiftedFess($id);
        $data['student'] = $this->student->getSingleStudent($id);
        $data['discount'] = $this->student->getStudentDiscount($id);
        $data['paid_fee'] = $this->student->getStudentPaidFee($id);
        $data['remaining_fee'] = $this->student->getStudentRemainingFee($id);
        $data['fee_should_pay'] = $this->student->getStudentFeeShouldPay($id);
        $data['consulation_fee'] = $this->student->getStudentConsulationFee($id);
        $data['consulation_fee_should_pay'] = $this->student->getStudentConsulationFeeShouldPay($id);
        $data['consulation_fee_paid'] = $this->student->getStudentConsulationFeePaid($id);
        $data['consulation_fee_unpaid'] = $this->student->getStudentConsulationFeeUnPaid($id);
        $data['total_fine'] = $this->student->getStudentTotalFine($id);
        $data['removed_fine'] = $this->student->getStudentRemovedFine($id);
        $data['fine_should_pay'] = $this->student->getStudentFineShouldPay($id);
        $data['fine_paid'] = $this->student->getStudentFinePaid($id);
        $data['total_calls'] = $this->student->getStudentTotalCalls($id);
        $data['total_extra_fee'] = $this->student->getStudentTotalExtraFee($id);
        $data['total_extra_paid_fee'] = $this->student->getStudentTotalExtraPaidFee($id);
        $data['extra_fee_paid_till_date'] = $this->student->getStudentExtraFeePaidTillDate($id);
        $data['extra_fee_remaining_till_date'] = $this->student->getStudentExtraFeeRemainingTillDate($id);
        $data['shift_delete_fee'] = $this->student->getStudentShiftDeleteFee($id);

        //$student_campus_id = $this->db->get_where('classes',array('class_id'=>$data['student'][0]['class_id']))->row()->campus_id;
        $data['account_numbers'] = $this->db->get_where('accounts',array('type'=>'1'))->result_array();

        $this->db->select('campuses.*');
        $this->db->from('campus_rules');
        $this->db->join('campuses','campuses.campus_id=campus_rules.campus_id','inner');
        $this->db->join('closing_persons','campuses.campus_id=closing_persons.campus_id','inner');
        $this->db->where('campus_rules.college_fee',1);
        $this->db->where('closing_persons.active_status',1);
        $data['campuses'] = $this->db->get()->result_array();

        //REVERSAL PAYMENT
        $this->db->select_sum('reversal_amount');
        $this->db->from('payments_reversal_requests');
        $this->db->where(array('student_id'=>$id,'done'=>1));
        $data['reversed_amount'] = $this->db->get()->result_array();
        
        //GET COUNCIL FEE TYPES
        $data['council_fees'] = $this->db->get_where('council_sequence',array('course_id'=>$data['student'][0]['course_id']))->result_array();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/payments_paid', $data);
        $this->load->view('inc/footer');
    }

    public function delete_payment($id,$student_id)
    {
        if($this->session->userdata('role')=='Admin'):
            $this->db->where('id', $id);
            $this->db->delete('payments');

            $this->db->where('id', $id);
            $this->db->delete('update_payment_requests');

            $this->session->set_flashdata('message','Fee Deleted Successfully');

            redirect('students/payments_paid/'.$student_id);
        endif;
    }

    public function delete_contractor_payment($id, $contract_id)
    {
        $this->db->where('id', $id);
        $this->db->delete('payments');

        $this->db->where('id', $id);
        $this->db->delete('update_payment_requests');

        $this->session->set_flashdata('message','Fee Deleted Successfully');

        redirect('contractors/contract_payments_paid/'.$contract_id);
    }

    public function contractor_payments_paid($contractor_id)
    {
        $data['payments'] = $this->student->contractor_payment_paid($contractor_id);
        $data['contractor'] = $this->student->getSingleContractor($contractor_id);
        $data['no_of_students'] = $this->student->getContractorStudents($contractor_id);

        $data['total_contract_amount'] = $this->student->getCompleteContractAmount($contractor_id);
        //$data['discount'] = $this->student->getStudentDiscount($contractor_id);
        $data['paid_fee'] = $this->student->getContractPaidFee($contractor_id);
        $data['remaining_fee'] = $this->student->getContractRemainingFee($contractor_id);
        $data['fee_should_pay'] = $this->student->getContractFeeShouldPay($contractor_id);

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/contractor_payments_paid', $data);
        $this->load->view('inc/footer');
    }

    public function paid_payment_action($student_id)
    {

        $this->load->helper('form');
        $config['upload_path'] = 'uploads/';
        $config['allowed_types'] = 'gif|jpg|png';
        //load the upload library
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->set_allowed_types('*');
        $data['upload_data'] = '';
        
        //if not successful, set the error message
        if (!$this->upload->do_upload('scan_challan')) {
            $scan_challan = '';

        } else { //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $scan_challan = $data['upload_data']['file_name'];
            }
        }
        
        //if not successful, set the error message
        if (!$this->upload->do_upload('fine_application')) {
            $fine_application = '';

        } else { //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $fine_application = $data['upload_data']['file_name'];
            }
        }

        
        $payment_ids = $this->input->post('fee_ids');
        $payment_ids=substr($payment_ids,1,strlen($payment_ids));
        $payment_ids = @explode(',',$payment_ids);

        if (count($payment_ids)>1) {
            $merged_challan=$this->getChallanNo();
        }
        else {
            $merged_challan = null;
        }

        $newfeegenerated = 0;
        $newfeeshifted = 0;
        $bill_url = "";

        $i=0;
        foreach ($payment_ids as $payment_id)
        {
            //CHECK FOR IF NEXT INSTALLMENT EXIST OR NOT
            $next_payment_id = $this->student->getNextPaymentId($payment_id, $student_id);

            if (@$this->input->post('prev_installment_status') == 'shift' || @$this->input->post('prev_fine_status') == 'shift' || @$this->input->post('late_fee_fine_status') == 'shift')
            {
                if (count($next_payment_id) < 1)
                {
                    $this->session->set_flashdata('error', 'Payment failed. There is no any installment remaining where remaining dues can be shifted.');
                    redirect('students/payments_paid/' . $student_id);
                }
            }

            //SHIFTED OR REMOVED OR NEW PARAMETERS CHECK
            $shifted_installment = 0;
            $new_installment = 0;
            $removed_previous_fine = 0;
            $shifted_previous_fine = 0;
            $new_previous_fine = 0;
            $removed_fine = 0;
            $shifted_fine = 0;
            $new_fine = 0;

            if (@$this->input->post('split_remaining_installment_amount') == 1)
            {
                if (@$this->input->post('prev_installment_status') == 'shift') {
                    $shifted_installment = @$this->input->post('remove_remaining_installment_amount');
                }
                if (@$this->input->post('prev_installment_status') == 'new') {
                    $new_installment = @$this->input->post('remove_remaining_installment_amount');
                }
            }
            if (@$this->input->post('split_remaining_fine_amount') == 1) {
                if (@$this->input->post('prev_fine_status') == 'remove') {
                    $removed_previous_fine = @$this->input->post('remove_previous_fine_amount');
                }
                if (@$this->input->post('prev_fine_status') == 'shift') {
                    $shifted_previous_fine = @$this->input->post('remove_previous_fine_amount');
                }
                if (@$this->input->post('prev_fine_status') == 'new') {
                    $new_previous_fine = @$this->input->post('remove_previous_fine_amount');
                }
            }
            if (@$this->input->post('split_fine_amount') == 1) {
                if (@$this->input->post('late_fee_fine_status') == 'remove') {
                    $removed_fine = @$this->input->post('remove_fine_amount');
                }
                if (@$this->input->post('late_fee_fine_status') == 'shift') {
                    $shifted_fine = @$this->input->post('remove_fine_amount');
                }
                if (@$this->input->post('late_fee_fine_status') == 'new') {
                    $new_fine = @$this->input->post('remove_fine_amount');
                }
            }

            if ($this->input->post('fee_pay_through') != 'pay_pro') {
                //SAVE PAYMENT QUERY
                if ($this->input->post('fee_pay_through') == 'bank') {

                    $acountid = $this->db->get_where('accounts', array('account_name' => $this->input->post('bank_details')))->row()->id;

                    if($i==0):
                        $this->db->select('*');
                        $this->db->from('bank_reconciliation_statement');
                        $this->db->where('account_id = "'.$acountid.'" and trans_date = "'.$this->input->post('paid_date').'" and (description like "%'.$this->input->post('tid_no').'%" or reference_no like "%'.$this->input->post('tid_no').'%") and CAST(REPLACE(credit,",","") as SIGNED) >= '.$this->input->post('actual_amount'));
                        $this->db->group_by("description");
                        $concile_details = $this->db->get()->result_array();

                        $this->db->set('tagged_amount', 'tagged_amount +' . $this->input->post('actual_amount') . '', false);
                        $this->db->where('id', $concile_details[0]['id']);
                        $this->db->update('bank_reconciliation_statement');
                    endif;

                    $data = array(
                        'scan_challan' => $scan_challan,
                        'merged_challan' => $merged_challan,
                        'paid_challans' => $this->input->post('challans'),
                        'fine_application' => $fine_application,
                        'actual_amount' => $this->input->post('actual_amount'),
                        'discount' => $this->input->post('discount'),
                        'id' => $payment_id,
                        'paid_date' => $this->input->post('paid_date'),
                        'paid' => 1,
                        'actual_paid_date' => date('Y-m-d'),
                        'college_fee' => 0,
                        'last_edit' => $this->session->userdata('name'),
                        'paid_by' => $this->session->userdata('name'),
                        'fee_pay_through' => $this->input->post('fee_pay_through'),
                        'bank_details' => $this->input->post('bank_details'),
                        'tid_no' => $this->input->post('tid_no'),
                        'fine_amount' => $this->input->post('fine_amount'),
                        'shifted_installment' => $shifted_installment + $new_installment,
                        'removed_previous_fine' => $removed_previous_fine,
                        'shifted_previous_fine' => $shifted_previous_fine + $new_previous_fine,
                        'removed_fine' => $removed_fine,
                        'submitted_fee_campus_id' => $this->input->post('submitted_fee_campus_id'),
                        'shifted_fine' => $shifted_fine + $new_fine,
                        'closing_id' => NULL,
                        'statement_id' => $concile_details[0]['id']
                    );
                } elseif ($this->input->post('fee_pay_through') == 'college' && $this->input->post('fee_submit_type') == 'computer_challan') {
                    $data = array(
                        'scan_challan' => $scan_challan,
                        'merged_challan' => $merged_challan,
                        'paid_challans' => $this->input->post('challans'),
                        'fine_application' => $fine_application,
                        'actual_amount' => $this->input->post('actual_amount'),
                        'discount' => $this->input->post('discount'),
                        'id' => $payment_id,
                        'paid_date' => $this->input->post('paid_date'),
                        'paid' => 1,
                        'actual_paid_date' => date('Y-m-d'),
                        'college_fee' => 0,
                        'last_edit' => $this->session->userdata('name'),
                        'paid_by' => $this->session->userdata('name'),
                        'fee_pay_through' => $this->input->post('fee_pay_through'),
                        'fee_submit_type' => $this->input->post('fee_submit_type'),
                        'fine_amount' => $this->input->post('fine_amount'),
                        'shifted_installment' => $shifted_installment + $new_installment,
                        'removed_previous_fine' => $removed_previous_fine,
                        'shifted_previous_fine' => $shifted_previous_fine + $new_previous_fine,
                        'removed_fine' => $removed_fine,
                        'submitted_fee_campus_id' => $this->input->post('submitted_fee_campus_id'),
                        'closing_id' => NULL,
                        'shifted_fine' => $shifted_fine + $new_fine
                    );
                } elseif ($this->input->post('fee_pay_through') == 'college' && $this->input->post('fee_submit_type') == 'receipt_book') {
                    $data = array(
                        'scan_challan' => $scan_challan,
                        'merged_challan' => $merged_challan,
                        'paid_challans' => $this->input->post('challans'),
                        'fine_application' => $fine_application,
                        'actual_amount' => $this->input->post('actual_amount'),
                        'discount' => $this->input->post('discount'),
                        'id' => $payment_id,
                        'paid_date' => $this->input->post('paid_date'),
                        'paid' => 1,
                        'actual_paid_date' => date('Y-m-d'),
                        'college_fee' => 0,
                        'last_edit' => $this->session->userdata('name'),
                        'paid_by' => $this->session->userdata('name'),
                        'fee_pay_through' => $this->input->post('fee_pay_through'),
                        'fee_submit_type' => $this->input->post('fee_submit_type'),
                        'submitted_fee_campus_id' => $this->input->post('submitted_fee_campus_id'),
                        'book_no' => $this->input->post('book_no'),
                        'receipt_no' => $this->input->post('receipt_no'),
                        'fine_amount' => $this->input->post('fine_amount'),
                        'shifted_installment' => $shifted_installment + $new_installment,
                        'removed_previous_fine' => $removed_previous_fine,
                        'shifted_previous_fine' => $shifted_previous_fine + $new_previous_fine,
                        'removed_fine' => $removed_fine,
                        'closing_id' => NULL,
                        'shifted_fine' => $shifted_fine + $new_fine
                    );
                } else {
                    $data = array();
                }
            }
            else{
                $paypro_challan = $this->getPayproID();
                $data = array(
                    'scan_challan' => $scan_challan,
//                    'merged_challan' => $paypro_challan,
//                    'paid_challans' => $this->input->post('challans'),
                    'fine_application' => $fine_application,
                    'actual_amount' => $this->input->post('actual_amount'),
                    'discount' => $this->input->post('discount'),
                    'id' => $payment_id,
                    'paid_date' => $this->input->post('paid_date'),
                    'actual_paid_date' => date('Y-m-d'),
                    'college_fee' => 0,
                    'last_edit' => $this->session->userdata('name'),
                    'paid_by' => $this->session->userdata('name'),
                    'fee_pay_through' => $this->input->post('fee_pay_through'),
                    'bank_details' => $this->input->post('bank_details'),
                    'tid_no' => $this->input->post('tid_no'),
                    'fine_amount' => $this->input->post('fine_amount'),
                    'shifted_installment' => $shifted_installment + $new_installment,
                    'removed_previous_fine' => $removed_previous_fine,
                    'shifted_previous_fine' => $shifted_previous_fine + $new_previous_fine,
                    'removed_fine' => $removed_fine,
                    'submitted_fee_campus_id' => $this->input->post('submitted_fee_campus_id'),
                    'shifted_fine' => $shifted_fine + $new_fine,
                    'closing_id' => NULL
                );
                $student = $this->db->get_where("students","student_id = '$student_id'")->row();
                $number = ($student->mobile != "" && $student->mobile != NULL) ? $student->mobile : $student->emergency_no;
                if ($number == "" || $number == NULL)
                    $number = "03168042977";
                $bill_url = $this->generate_paypro($this->input->post('actual_amount'),$student->first_name.' '.$student->last_name,$number,$paypro_challan,$student_id,$this->input->post('challans'));
            }
            //SAVE INSTALLMENT
            $this->student->saveInstallment($data);

            //SHIFT PAYMENT TO NEXT PAYMENT
            if (($shifted_installment > 0 || $shifted_previous_fine > 0 || $shifted_fine > 0) && $newfeeshifted == 0) {
                $next_payment_id = $next_payment_id[0]['id'];
                $shift_fine_amount = $shifted_previous_fine + $shifted_fine;
                $shift_installment_amount = $shifted_installment;
                $current_installment_challan = $this->db->get_where('payments', array('id' => $payment_id))->row()->challan_no;

                $this->student->addExtraChargesToNextInstallment($next_payment_id, $shift_fine_amount, $shift_installment_amount, $current_installment_challan);

                $newfeeshifted = 1;
            }

            //ADD NEW PAYMENT
            if (($new_installment > 0 || $new_previous_fine > 0 || $new_fine > 0)&& $newfeegenerated == 0) {
                $current_installment_challan = $this->db->get_where('payments', array('id' => $payment_id))->row()->challan_no;
                $new_dead_line = $this->input->post('new_dead_line');
                $this->student->addExtraChargesToNewInstallment($student_id, $new_dead_line, $new_installment, $new_previous_fine, $new_fine, $current_installment_challan);
                $newfeegenerated = 1;
            }

            //SEND SMS TO STUDENT
            $this->feeSmsAlertToStudent($student_id, $this->input->post('paid_date'), $this->input->post('actual_amount'));
            //SEND SMS TO CONTROL CENTER
            $this->feeSmsAlertToControlCenter($student_id, $this->input->post('paid_date'), $this->input->post('actual_amount'), $this->input->post('college_fee'), $this->input->post('add_by'));

            $i++;
        }

        $contract=$this->db->get_where('payments','id = "'.$payment_ids[0].'"')->row()->contract_id;

        if ($bill_url != ""){
            redirect($bill_url, 'refresh');
        } else{
            if ($contract == '0') {
                $this->session->set_flashdata('message', 'Fee submitted successfully.');
                redirect('students/payments_paid/' . $student_id);
            } else {
                $this->session->set_flashdata('message', 'Fee submitted successfully.');
                redirect('contractors/contract_payments_paid/' . $contract);
            }
        }
    }

    public function contractor_paid_payment_action($contractor_id)
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
        if (!$this->upload->do_upload('scan_challan')) {
            $data = array('msg' => $this->upload->display_errors());
            $scan_challan = '';

        } else { //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $scan_challan = $data['upload_data']['file_name'];
            }
        }

        //if not successful, set the error message
        if (!$this->upload->do_upload('fine_application')) {
            $data = array('msg' => $this->upload->display_errors());
            $fine_application = '';

        } else { //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $fine_application = $data['upload_data']['file_name'];
            }
        }

        //CHECK TO CHECK AMOUNT

        //echo $this->input->post('dead_line');
        $dead_line_date = date_create($this->input->post('dead_line'));
        $payment_paid_date = date_create($this->input->post('paid_date'));
        $diff=date_diff($dead_line_date,$payment_paid_date);
        $difference = $diff->format("%R%a");
        //echo $difference;
        if($difference>0)
        {
            $payment_plan = $this->input->post('payment_plan');
            if($payment_plan=='24 Installments')
            {
                $fine = $difference*10;
            }
            else
            {
                $fine = $difference*50;
            }
        }
        else
        {
            $fine = 0;
        }
        $amount_should_paid = $this->input->post('fee_amount')+$fine;


        if( $this->input->post('actual_amount') < $amount_should_paid)
        {
            $payment_id = $this->input->post('id');
            $next_payment_id = $this->student->getNextPaymentIdContractor($payment_id, $contractor_id);

            if($next_payment_id!='' && count($next_payment_id)>0)
            {
                $data = array(
                    'scan_challan' => $scan_challan,
                    'fine_application' => $fine_application,
                    'actual_amount' => $this->input->post('actual_amount'),
                    'id' => $this->input->post('id'),
                    'paid_date' => $this->input->post('paid_date'),
                    'paid' => 1,
                    'college_fee' => $this->input->post('college_fee'),
                    'paid_by'				=> $this->session->userdata('name'),
                );
                $next_payment_id = $next_payment_id[0]['id'];
                $extra_amount = $amount_should_paid - $this->input->post('actual_amount');
                $this->student->addExtraChargesToNextInstallment($next_payment_id, $extra_amount);
                $this->student->saveInstallment($data);
            }
            else
            {
                $this->session->set_flashdata('error', 'Fee submitted failed');
                redirect('students/contractor_payments_paid/'.$contractor_id);
            }
        }
        else
        {
            $data = array(
                'scan_challan' => $scan_challan,
                'fine_application' => $fine_application,
                'actual_amount' => $this->input->post('actual_amount'),
                'id' => $this->input->post('id'),
                'paid_date' => $this->input->post('paid_date'),
                'paid' => 1,
                'college_fee' => $this->input->post('college_fee'),
                'paid_by'				=> $this->session->userdata('name'),
            );
            $this->student->saveInstallment($data);
        }
        redirect('students/contractor_payments_paid/'.$contractor_id);
    }

    public function print_challan($challan_id)
    {

        $challan_id = rtrim($challan_id, ", ");
        $array = explode(',' ,$challan_id);

        $chals= $this->student->challan($challan_id);

        $obj=$chals[0];
        $obj['merged_challan']=$obj['challan_no'];
        $obj['totamount']=$obj['amount'];
        $obj['discount']=0;
        $today_date = date_create(date('Y-m-d'));
        unset($chals[0]);

        foreach ($chals as $ch){

            $obj['merged_challan'].=($ch['challan_no']." ");
            $obj['amount'].=('+'.$ch['amount']);
            $obj['totamount']+=$ch['amount'];


            $challan_date = date_create($ch['dead_line']);


            $diff=date_diff($challan_date,$today_date);
            $difference = $diff->format("%R%a");

            if ($difference < -30) {

                $obj['discount'] += $ch['disc_per_inst'];

            }



        }
        $result= array();

        array_push($result ,$obj);

        $data['challans']  = $result;

        $this->load->view('students/print_challan', $data);

    }

    public function print_contractor_challan($challan_id)
    {
        $data['challans'] = $this->student->contractor_challan($challan_id);

        $this->load->view('contractors/print_contractor_challan', $data);
    }

    public function print_college_challan($challan_id,$type = null)
    {
        $data['challans'] = $this->student->challan($challan_id);
        $data['type'] = $type;
        $data['challan_id'] = $challan_id;
        $this->load->view('students/print_college_challan', $data);
    }

    public function print_contractor_college_challan($challan_id)
    {
        $data['challans'] = $this->student->contractor_challan($challan_id);

        $this->load->view('students/print_contractor_college_challan', $data);
    }

    public function edit_payment($payment_id,$instno,$iscontract = 0)
    {
        $data['payments'] = $this->student->editPayment($payment_id);
        $data['instno'] = $instno;

        if ($iscontract == 0) {
            $data['student'] = $this->db->get_where('students', array('student_id' => $data['payments'][0]['student_id']))->result_array();
            $student_campus_id = $this->db->get_where('classes', array('class_id' => $data['student'][0]['class_id']))->row()->campus_id;
        }
        else {
            $data['student'] = $this->db->get_where('contracts', array('contract_id' => $data['payments'][0]['contract_id']))->result_array();
            $student_campus_id = $data['student'][0]['campus_id'];
        }

        $data['account_numbers'] = $this->db->get_where('accounts',array('type'=>'1'))->result_array();

        $this->db->select('campuses.*');
        $this->db->from('campus_rules');
        $this->db->join('campuses','campuses.campus_id=campus_rules.campus_id','inner');
        $this->db->where('campus_rules.college_fee',1);
        $data['campuses'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/edit_payment', $data);
        $this->load->view('inc/footer');
    }

    public function update_edit_payment($update_type,$payment_id,$payment_no='')
    {
        $payment_details = $this->db->get_where('payments',array('id'=>$payment_id))->result_array();
        $checkRequest = $this->student->checkChallanRequest($payment_id);
        if(count($checkRequest)>0)
        {
            $this->session->set_flashdata('error', 'This Challan Request is already been submitted. Kindly clear your previous request first.');
            if($this->input->post('contract_id')>0)
            {
                redirect(site_url().'/contractors/contract_payments_paid/'.$this->input->post('contract_id'));
            }
            else
            {
                redirect(site_url().'/students/payments_paid/'.$payment_details[0]['student_id']);
            }
        }

        if($update_type=='paid_date')
        {
            $paid_date = $this->input->post('paid_date');
            $reason = $this->input->post('reason');

            //UPDATE EXTEND DATE IN PAYMENTS
            $this->db->set('paid_date',$paid_date);
            $this->db->set('clear_college_fee',0);
            $this->db->set('clear_by','');
            $this->db->where('id',$payment_details[0]['id']);
            $this->db->update('payments');

            //ADD RECORD IN UPDATE PAYMENT REQUEST TABLE
            $this->db->set('id',$payment_details[0]['id']);
            $this->db->set('amount',$payment_details[0]['amount']);
            $this->db->set('challan_no',$payment_details[0]['challan_no']);
            $this->db->set('dead_line',$payment_details[0]['dead_line']);
            $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
            $this->db->set('scan_challan',$payment_details[0]['scan_challan']);
            $this->db->set('fine_application',$payment_details[0]['fine_application']);
            $this->db->set('actual_amount',$payment_details[0]['actual_amount']);
            $this->db->set('paid',$payment_details[0]['paid']);
            $this->db->set('student_id',$payment_details[0]['student_id']);
            $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
            $this->db->set('contract_id',$payment_details[0]['contract_id']);
            $this->db->set('paid_date',$paid_date);
            $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
            $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
            $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
            $this->db->set('college_fee',$payment_details[0]['college_fee']);
            $this->db->set('clear_college_fee',$payment_details[0]['clear_college_fee']);
            $this->db->set('add_by',$payment_details[0]['add_by']);
            $this->db->set('last_edit',$this->session->userdata['name']);
            $this->db->set('reason',$reason);
            $this->db->set('fee_pay_through',$payment_details[0]['fee_pay_through']);
            $this->db->set('bank_details',$payment_details[0]['bank_details']);
            $this->db->set('tid_no',$payment_details[0]['tid_no']);
            $this->db->set('submitted_fee_campus_id',$payment_details[0]['submitted_fee_campus_id']);
            $this->db->set('book_no',$payment_details[0]['book_no']);
            $this->db->set('receipt_no',$payment_details[0]['receipt_no']);
            $this->db->set('ok_by_admin',1);
            $this->db->set('clear_by','System');

            $this->db->insert('update_payment_requests');
        }

        if($update_type=='payment_method')
        {
//            $supported_image = array('image/gif', 'image/jpg', 'image/jpeg', 'image/png');
//
//            if (in_array($_FILES['scan_challan']['type'], $supported_image)) {
//
//                $src_file_name = $_FILES['scan_challan']['name'];
//
//                if (!file_exists(getcwd().'/uploads')) {
//
//                    mkdir(getcwd().'/uploads', 0777);
//                }
//
//                move_uploaded_file($_FILES['scan_challan']['tmp_name'], getcwd().'/uploads/'.$src_file_name);
//
//                //optimize image using TinyPNG
//                $source = \Tinify\fromFile(getcwd().'/uploads/'.$src_file_name);
//                $source->toFile(getcwd().'/uploads/'.$src_file_name);
//                $scan_challan=$src_file_name;
//
//
//            }
//            else {
//                $data = array('msg' => $this->upload->display_errors());
//                $scan_challan = '';
//            }

            $bank_details = $this->input->post('bank_details');
            $tid_no = $this->input->post('tid_no');
            $acountid = $this->db->get_where('accounts', array('account_name' => $bank_details))->row()->id;

            if ($payment_details[0]['statement_id'] != null && $payment_details[0]['statement_id'] != '') {
                $this->db->set('tagged_amount', 'tagged_amount -' . $payment_details[0]['actual_amount'] . '', false);
                $this->db->where('id', $payment_details[0]['statement_id']);
                $this->db->update('bank_reconciliation_statement');
            }

            $this->db->select('*');
            $this->db->from('bank_reconciliation_statement');
            $this->db->where('account_id = "'.$acountid.'" and trans_date = "'.$this->input->post('paid_date').'" and (description like "%'.$tid_no.'%" or reference_no like "%'.$tid_no.'%") and CAST(REPLACE(credit,",","") as SIGNED) >= '.$payment_details[0]['actual_amount']);
//                    $this->db->where('account_id = "' . $acountid . '" and (description like "%' . $this->input->post('tid_no') . '%" or reference_no like "%' . $this->input->post('tid_no') . '%")');
            $this->db->group_by("description");
            $concile_details = $this->db->get()->result_array();


            //UPDATE IN PAYMENTS TABLE
            $this->db->set('bank_details',$bank_details);
            $this->db->set('tid_no',$tid_no);
            $this->db->set('statement_id',$concile_details[0]['id']);
            $this->db->set('clear_college_fee',0);
            $this->db->set('paid_date',$this->input->post('paid_date'));
            //if ($scan_challan != '')
            //    $this->db->set('scan_challan',$scan_challan);
            $this->db->set('clear_by','');
            $this->db->where('id',$payment_details[0]['id']);
            $this->db->update('payments');
        }

        if($update_type=='payment_status')
        {
            $status = $this->input->post('status');
            $reason = $this->input->post('reason');

            $this->db->set('id',$payment_details[0]['id']);
            $this->db->set('amount',$payment_details[0]['amount']);
            $this->db->set('challan_no',$payment_details[0]['challan_no']);
            $this->db->set('dead_line',$payment_details[0]['dead_line']);
            $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
            $this->db->set('scan_challan','');
            $this->db->set('fine_application','');
            $this->db->set('actual_amount',0);
            $this->db->set('paid',$status);
            $this->db->set('student_id',$payment_details[0]['student_id']);
            $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
            $this->db->set('contract_id',$payment_details[0]['contract_id']);
            $this->db->set('paid_date','0000-00-00');
            $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
            $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
            $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
            $this->db->set('college_fee',$payment_details[0]['college_fee']);
            $this->db->set('clear_college_fee',0);
            $this->db->set('add_by',$payment_details[0]['add_by']);
            $this->db->set('last_edit',$this->session->userdata['name']);
            $this->db->set('reason',$reason);
            $this->db->set('fee_pay_through','');
            $this->db->set('bank_details','');
            $this->db->set('tid_no','');
            $this->db->set('submitted_fee_campus_id',0);
            $this->db->set('book_no','');
            $this->db->set('receipt_no','');

            $this->db->insert('update_payment_requests');
        }

        if($update_type=='payment_image')
        {
//            if($payment_details[0]['student_id']==0)
//            {
//                if (!is_dir('student_fee_challans/contractor_'.$payment_details[0]['contractor_id'])) {
//                    mkdir('./student_fee_challans/contractor_'.$payment_details[0]['contractor_id'], 0777, TRUE);
//                }
//            }
//            else
//            {
//                if (!is_dir('student_fee_challans/student_'.$payment_details[0]['student_id'])) {
//                    mkdir('./student_fee_challans/student_'.$payment_details[0]['student_id'], 0777, TRUE);
//                }
//            }

            //load the helper
            $this->load->helper('form');
            //Configure
            //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
            $config['upload_path'] = 'uploads/';
//            if($payment_details[0]['student_id']==0)
//            {
//                $config['upload_path'] = 'student_fee_challans/contractor_'.$payment_details[0]['contractor_id'];
//            }
//            else
//            {
//                $config['upload_path'] = 'student_fee_challans/student_'.$payment_details[0]['student_id'];
//            }
            // set the filter image types
            $config['allowed_types'] = 'gif|jpg|png';
            //load the upload library
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            $this->upload->set_allowed_types('*');
            $data['upload_data'] = '';
            //if not successful, set the error message
            if (!$this->upload->do_upload('new_scan_challan')) {
                $scan_challan = '';

            } else { //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if($data['upload_data']['file_name']){
                    $scan_challan = $data['upload_data']['file_name'];
                }
            }

            //UPDATE IN PAYMENTS TABLE
            $this->db->set('scan_challan',$scan_challan);
            $this->db->set('clear_college_fee',0);
            $this->db->set('clear_by','');
            $this->db->where('id',$payment_details[0]['id']);
            $this->db->update('payments');

            //UPDATE IN UPDATE PAYMENTS TABLE

            $reason = $this->input->post('reason');

            $this->db->set('id',$payment_details[0]['id']);
            $this->db->set('amount',$payment_details[0]['amount']);
            $this->db->set('challan_no',$payment_details[0]['challan_no']);
            $this->db->set('dead_line',$payment_details[0]['dead_line']);
            $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
            $this->db->set('scan_challan',$scan_challan);
            $this->db->set('fine_application',$payment_details[0]['fine_application']);
            $this->db->set('actual_amount',$payment_details[0]['actual_amount']);
            $this->db->set('paid',$payment_details[0]['paid']);
            $this->db->set('student_id',$payment_details[0]['student_id']);
            $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
            $this->db->set('contract_id',$payment_details[0]['contract_id']);
            $this->db->set('paid_date',$payment_details[0]['paid_date']);
            $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
            $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
            $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
            $this->db->set('college_fee',$payment_details[0]['college_fee']);
            $this->db->set('clear_college_fee',$payment_details[0]['clear_college_fee']);
            $this->db->set('add_by',$payment_details[0]['add_by']);
            $this->db->set('last_edit',$this->session->userdata['name']);
            $this->db->set('reason',$reason);
            $this->db->set('fee_pay_through',$payment_details[0]['fee_pay_through']);
            $this->db->set('bank_details',$payment_details[0]['bank_details']);
            $this->db->set('tid_no',$payment_details[0]['tid_no']);
            $this->db->set('submitted_fee_campus_id',$payment_details[0]['submitted_fee_campus_id']);
            $this->db->set('book_no',$payment_details[0]['book_no']);
            $this->db->set('receipt_no',$payment_details[0]['receipt_no']);
            $this->db->set('ok_by_admin',1);
            $this->db->set('clear_by','System');

            $this->db->insert('update_payment_requests');
        }

        if($update_type=='extend_date')
        {
            $new_dead_line = $this->input->post('new_dead_line');
            $reason = $this->input->post('reason');

            $date1=date_create($new_dead_line);
            $date2=date_create($payment_details[0]['dead_line']);
            $diff=date_diff($date1,$date2)->days;

            $already_requested = $this->db->get_where('update_payment_requests',array('id'=>$payment_details[0]['id']))->result_array();

            if($diff<32 && count($already_requested)<1)
            {
                //UPDATE EXTEND DATE IN PAYMENTS
                $this->db->set('dead_line',$new_dead_line);
                $this->db->set('clear_college_fee',0);
                $this->db->set('clear_by','');
                $this->db->where('id',$payment_details[0]['id']);
                $this->db->update('payments');

                //ADD RECORD IN UPDATE PAYMENT REQUEST TABLE
                $this->db->set('id',$payment_details[0]['id']);
                $this->db->set('amount',$payment_details[0]['amount']);
                $this->db->set('challan_no',$payment_details[0]['challan_no']);
                $this->db->set('dead_line',$new_dead_line);
                $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
                $this->db->set('scan_challan',$payment_details[0]['scan_challan']);
                $this->db->set('fine_application',$payment_details[0]['fine_application']);
                $this->db->set('actual_amount',$payment_details[0]['actual_amount']);
                $this->db->set('paid',$payment_details[0]['paid']);
                $this->db->set('student_id',$payment_details[0]['student_id']);
                $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
                $this->db->set('contract_id',$payment_details[0]['contract_id']);
                $this->db->set('paid_date',$payment_details[0]['paid_date']);
                $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
                $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
                $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
                $this->db->set('college_fee',$payment_details[0]['college_fee']);
                $this->db->set('clear_college_fee',$payment_details[0]['clear_college_fee']);
                $this->db->set('add_by',$payment_details[0]['add_by']);
                $this->db->set('last_edit',$this->session->userdata['name']);
                $this->db->set('reason',$reason);
                $this->db->set('fee_pay_through',$payment_details[0]['fee_pay_through']);
                $this->db->set('bank_details',$payment_details[0]['bank_details']);
                $this->db->set('tid_no',$payment_details[0]['tid_no']);
                $this->db->set('submitted_fee_campus_id',$payment_details[0]['submitted_fee_campus_id']);
                $this->db->set('book_no',$payment_details[0]['book_no']);
                $this->db->set('receipt_no',$payment_details[0]['receipt_no']);
                $this->db->set('ok_by_admin',1);
                $this->db->set('clear_by','System');

                $this->db->insert('update_payment_requests');
            }
            else
            {
                $this->db->set('id',$payment_details[0]['id']);
                $this->db->set('amount',$payment_details[0]['amount']);
                $this->db->set('challan_no',$payment_details[0]['challan_no']);
                $this->db->set('dead_line',$new_dead_line);
                $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
                $this->db->set('scan_challan',$payment_details[0]['scan_challan']);
                $this->db->set('fine_application',$payment_details[0]['fine_application']);
                $this->db->set('actual_amount',$payment_details[0]['actual_amount']);
                $this->db->set('paid',$payment_details[0]['paid']);
                $this->db->set('student_id',$payment_details[0]['student_id']);
                $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
                $this->db->set('contract_id',$payment_details[0]['contract_id']);
                $this->db->set('paid_date',$payment_details[0]['paid_date']);
                $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
                $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
                $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
                $this->db->set('college_fee',$payment_details[0]['college_fee']);
                $this->db->set('clear_college_fee',$payment_details[0]['clear_college_fee']);
                $this->db->set('add_by',$payment_details[0]['add_by']);
                $this->db->set('last_edit',$this->session->userdata['name']);
                $this->db->set('reason',$reason);
                $this->db->set('fee_pay_through',$payment_details[0]['fee_pay_through']);
                $this->db->set('bank_details',$payment_details[0]['bank_details']);
                $this->db->set('tid_no',$payment_details[0]['tid_no']);
                $this->db->set('submitted_fee_campus_id',$payment_details[0]['submitted_fee_campus_id']);
                $this->db->set('book_no',$payment_details[0]['book_no']);
                $this->db->set('receipt_no',$payment_details[0]['receipt_no']);

                $this->db->insert('update_payment_requests');
            }
        }

        if($update_type=='delete_payment')
        {
            $reason = $this->input->post('reason');

            $this->db->set('id',$payment_details[0]['id']);
            $this->db->set('amount',$payment_details[0]['amount']);
            $this->db->set('challan_no',$payment_details[0]['challan_no']);
            $this->db->set('dead_line',$payment_details[0]['dead_line']);
            $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
            $this->db->set('scan_challan',$payment_details[0]['scan_challan']);
            $this->db->set('fine_application',$payment_details[0]['fine_application']);
            $this->db->set('actual_amount',$payment_details[0]['actual_amount']);
            $this->db->set('paid',$payment_details[0]['paid']);
            $this->db->set('student_id',$payment_details[0]['student_id']);
            $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
            $this->db->set('contract_id',$payment_details[0]['contract_id']);
            $this->db->set('paid_date',$payment_details[0]['paid_date']);
            $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
            $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
            $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
            $this->db->set('college_fee',$payment_details[0]['college_fee']);
            $this->db->set('clear_college_fee',$payment_details[0]['clear_college_fee']);
            $this->db->set('add_by',$payment_details[0]['add_by']);
            $this->db->set('last_edit',$this->session->userdata['name']);
            $this->db->set('reason',$reason);
            $this->db->set('fee_pay_through',$payment_details[0]['fee_pay_through']);
            $this->db->set('bank_details',$payment_details[0]['bank_details']);
            $this->db->set('tid_no',$payment_details[0]['tid_no']);
            $this->db->set('submitted_fee_campus_id',$payment_details[0]['submitted_fee_campus_id']);
            $this->db->set('book_no',$payment_details[0]['book_no']);
            $this->db->set('receipt_no',$payment_details[0]['receipt_no']);
            $this->db->set('del',1);

            $this->db->insert('update_payment_requests');
        }

        if($update_type=='payment_discount')
        {

            $this->db->select('*');
            $this->db->from('discounts_approval');
            $this->db->where("student_id = '".$payment_id."' and status='0'");
            $checkRequest = $this->db->get()->result_array();

            if(count($checkRequest)>0)
            {

                //print_r($checkRequest);
                //exit();
                $this->session->set_flashdata('error', 'Fee Discount Request is already been submitted. Kindly clear your previous request first.');

                redirect(site_url().'/students/payments_paid/'.$payment_id);

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



            $disc = $this->input->post('apldiscount');
            $reasondisc = $this->input->post('reason_disc');
            $rem = $this->input->post('remainfee');


            $this->db->set('student_id',$payment_id);
            $this->db->set('remaining_fee',$rem);
            $this->db->set('reason',$reasondisc);
            $this->db->set('discount',$disc);
            $this->db->set('application',$image);
            $this->db->set('created_by',$this->session->userdata('name'));

            $this->db->insert('discounts_approval');
            redirect(site_url().'/students/payments_paid/'.$payment_id);
        }

        if($payment_details[0]['contract_id']>0)
        {
            $this->session->set_flashdata('message', 'Payment request submitted successfully');
            redirect(site_url().'/contractors/contract_payments_paid/'.$payment_details[0]['contract_id']);
        }
        else
        {
            $this->session->set_flashdata('message', 'Payment request submitted successfully');
            redirect(site_url().'/students/payments_paid/'.$payment_details[0]['student_id']);
        }
    }

//    public function update_edit_payment($update_type,$payment_id)
//    {
//        $payment_details = $this->db->get_where('payments',array('id'=>$payment_id))->result_array();
//        $checkRequest = $this->student->checkChallanRequest($payment_id);
//        if(count($checkRequest)>0)
//        {
//            $this->session->set_flashdata('error', 'This Challan Request is already been submitted. Kindly clear your previous request first.');
//            if($this->input->post('contract_id')>0)
//            {
//                redirect(site_url().'/contractors/contract_payments_paid/'.$this->input->post('contract_id'));
//            }
//            else
//            {
//                redirect(site_url().'/students/payments_paid/'.$payment_details[0]['student_id']);
//            }
//        }
//
//        if($update_type=='paid_date')
//        {
//            $paid_date = $this->input->post('paid_date');
//            $reason = $this->input->post('reason');
//
//            //UPDATE EXTEND DATE IN PAYMENTS
//            $this->db->set('paid_date',$paid_date);
//            $this->db->set('clear_college_fee',0);
//            $this->db->set('clear_by','');
//            $this->db->where('id',$payment_details[0]['id']);
//            $this->db->update('payments');
//
//            //ADD RECORD IN UPDATE PAYMENT REQUEST TABLE
//            $this->db->set('id',$payment_details[0]['id']);
//            $this->db->set('amount',$payment_details[0]['amount']);
//            $this->db->set('challan_no',$payment_details[0]['challan_no']);
//            $this->db->set('dead_line',$payment_details[0]['dead_line']);
//            $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
//            $this->db->set('scan_challan',$payment_details[0]['scan_challan']);
//            $this->db->set('fine_application',$payment_details[0]['fine_application']);
//            $this->db->set('actual_amount',$payment_details[0]['actual_amount']);
//            $this->db->set('paid',$payment_details[0]['paid']);
//            $this->db->set('student_id',$payment_details[0]['student_id']);
//            $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
//            $this->db->set('contract_id',$payment_details[0]['contract_id']);
//            $this->db->set('paid_date',$paid_date);
//            $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
//            $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
//            $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
//            $this->db->set('college_fee',$payment_details[0]['college_fee']);
//            $this->db->set('clear_college_fee',$payment_details[0]['clear_college_fee']);
//            $this->db->set('add_by',$payment_details[0]['add_by']);
//            $this->db->set('last_edit',$this->session->userdata['name']);
//            $this->db->set('reason',$reason);
//            $this->db->set('fee_pay_through',$payment_details[0]['fee_pay_through']);
//            $this->db->set('bank_details',$payment_details[0]['bank_details']);
//            $this->db->set('tid_no',$payment_details[0]['tid_no']);
//            $this->db->set('submitted_fee_campus_id',$payment_details[0]['submitted_fee_campus_id']);
//            $this->db->set('book_no',$payment_details[0]['book_no']);
//            $this->db->set('receipt_no',$payment_details[0]['receipt_no']);
//            $this->db->set('ok_by_admin',1);
//            $this->db->set('clear_by','System');
//
//            $this->db->insert('update_payment_requests');
//        }
//
//        if($update_type=='payment_method')
//        {
//            $fee_pay_through = $this->input->post('fee_pay_through');
//            $bank_details = $this->input->post('bank_details');
//            $tid_no = $this->input->post('tid_no');
//            $fee_submit_type = $this->input->post('fee_submit_type');
//            $submitted_fee_campus_id = $this->input->post('submitted_fee_campus_id');
//            $book_no = $this->input->post('book_no');
//            $receipt_no = $this->input->post('receipt_no');
//            $reason = $this->input->post('reason');
//
//            //UPDATE IN PAYMENTS TABLE
//            $this->db->set('fee_pay_through',$fee_pay_through);
//            if($fee_pay_through=='bank')
//            {
//                $this->db->set('submitted_fee_campus_id',0);
//                $this->db->set('book_no','');
//                $this->db->set('receipt_no','');
//                $this->db->set('fee_submit_type','');
//                $this->db->set('bank_details',$bank_details);
//                $this->db->set('tid_no',$tid_no);
//            }
//            if($fee_pay_through=='college' && $fee_submit_type=='computer_challan')
//            {
//                $this->db->set('bank_details','');
//                $this->db->set('tid_no','');
//                $this->db->set('submitted_fee_campus_id',0);
//                $this->db->set('book_no','');
//                $this->db->set('receipt_no','');
//                $this->db->set('fee_submit_type',$fee_submit_type);
//            }
//            if($fee_pay_through=='college' && $fee_submit_type=='receipt_book')
//            {
//                $this->db->set('bank_details','');
//                $this->db->set('tid_no','');
//                $this->db->set('fee_submit_type',$fee_submit_type);
//                $this->db->set('submitted_fee_campus_id',$submitted_fee_campus_id);
//                $this->db->set('book_no',$book_no);
//                $this->db->set('receipt_no',$receipt_no);
//            }
//            $this->db->set('clear_college_fee',0);
//            $this->db->set('clear_by','');
//            $this->db->where('id',$payment_details[0]['id']);
//            $this->db->update('payments');
//
//            //INSERT DATA IN UPDATE PAYMENT REQUEST
//            $this->db->set('id',$payment_details[0]['id']);
//            $this->db->set('amount',$payment_details[0]['amount']);
//            $this->db->set('challan_no',$payment_details[0]['challan_no']);
//            $this->db->set('dead_line',$payment_details[0]['dead_line']);
//            $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
//            $this->db->set('scan_challan',$payment_details[0]['scan_challan']);
//            $this->db->set('fine_application',$payment_details[0]['fine_application']);
//            $this->db->set('actual_amount',$payment_details[0]['actual_amount']);
//            $this->db->set('paid',$payment_details[0]['paid']);
//            $this->db->set('student_id',$payment_details[0]['student_id']);
//            $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
//            $this->db->set('contract_id',$payment_details[0]['contract_id']);
//            $this->db->set('paid_date',$payment_details[0]['paid_date']);
//            $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
//            $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
//            $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
//            $this->db->set('college_fee',$payment_details[0]['college_fee']);
//            $this->db->set('clear_college_fee',$payment_details[0]['clear_college_fee']);
//            $this->db->set('add_by',$payment_details[0]['add_by']);
//            $this->db->set('last_edit',$this->session->userdata['name']);
//            $this->db->set('reason',$reason);
//            $this->db->set('fee_pay_through',$fee_pay_through);
//            if($fee_pay_through=='bank')
//            {
//                $this->db->set('bank_details',$bank_details);
//                $this->db->set('tid_no',$tid_no);
//            }
//            if($fee_pay_through=='college' && $fee_submit_type=='computer_challan')
//            {
//
//            }
//            if($fee_pay_through=='college' && $fee_submit_type=='receipt_book')
//            {
//                $this->db->set('submitted_fee_campus_id',$submitted_fee_campus_id);
//                $this->db->set('book_no',$book_no);
//                $this->db->set('receipt_no',$receipt_no);
//            }
//            $this->db->set('ok_by_admin',1);
//            $this->db->set('clear_by','System');
//
//            $this->db->insert('update_payment_requests');
//        }
//
//        if($update_type=='payment_status')
//        {
//            $status = $this->input->post('status');
//            $reason = $this->input->post('reason');
//
//            $this->db->set('id',$payment_details[0]['id']);
//            $this->db->set('amount',$payment_details[0]['amount']);
//            $this->db->set('challan_no',$payment_details[0]['challan_no']);
//            $this->db->set('dead_line',$payment_details[0]['dead_line']);
//            $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
//            $this->db->set('scan_challan','');
//            $this->db->set('fine_application','');
//            $this->db->set('actual_amount',0);
//            $this->db->set('paid',$status);
//            $this->db->set('student_id',$payment_details[0]['student_id']);
//            $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
//            $this->db->set('contract_id',$payment_details[0]['contract_id']);
//            $this->db->set('paid_date','0000-00-00');
//            $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
//            $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
//            $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
//            $this->db->set('college_fee',$payment_details[0]['college_fee']);
//            $this->db->set('clear_college_fee',0);
//            $this->db->set('add_by',$payment_details[0]['add_by']);
//            $this->db->set('last_edit',$this->session->userdata['name']);
//            $this->db->set('reason',$reason);
//            $this->db->set('fee_pay_through','');
//            $this->db->set('bank_details','');
//            $this->db->set('tid_no','');
//            $this->db->set('submitted_fee_campus_id',0);
//            $this->db->set('book_no','');
//            $this->db->set('receipt_no','');
//
//            $this->db->insert('update_payment_requests');
//        }
//
//        if($update_type=='payment_image')
//        {
////            if($payment_details[0]['student_id']==0)
////            {
////                if (!is_dir('student_fee_challans/contractor_'.$payment_details[0]['contractor_id'])) {
////                    mkdir('./student_fee_challans/contractor_'.$payment_details[0]['contractor_id'], 0777, TRUE);
////                }
////            }
////            else
////            {
////                if (!is_dir('student_fee_challans/student_'.$payment_details[0]['student_id'])) {
////                    mkdir('./student_fee_challans/student_'.$payment_details[0]['student_id'], 0777, TRUE);
////                }
////            }
//
//            //load the helper
//            $this->load->helper('form');
//            //Configure
//            //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
//            $config['upload_path'] = 'uploads/';
////            if($payment_details[0]['student_id']==0)
////            {
////                $config['upload_path'] = 'student_fee_challans/contractor_'.$payment_details[0]['contractor_id'];
////            }
////            else
////            {
////                $config['upload_path'] = 'student_fee_challans/student_'.$payment_details[0]['student_id'];
////            }
//            // set the filter image types
//            $config['allowed_types'] = 'gif|jpg|png';
//            //load the upload library
//            $this->load->library('upload', $config);
//            $this->upload->initialize($config);
//            $this->upload->set_allowed_types('*');
//            $data['upload_data'] = '';
//            //if not successful, set the error message
//            if (!$this->upload->do_upload('new_scan_challan')) {
//                $scan_challan = '';
//
//            } else { //else, set the success message
//                $data['upload_data'] = $this->upload->data();
//                if($data['upload_data']['file_name']){
//                    $scan_challan = $data['upload_data']['file_name'];
//                }
//            }
//
//            //UPDATE IN PAYMENTS TABLE
//            $this->db->set('scan_challan',$scan_challan);
//            $this->db->set('clear_college_fee',0);
//            $this->db->set('clear_by','');
//            $this->db->where('id',$payment_details[0]['id']);
//            $this->db->update('payments');
//
//            //UPDATE IN UPDATE PAYMENTS TABLE
//
//            $reason = $this->input->post('reason');
//
//            $this->db->set('id',$payment_details[0]['id']);
//            $this->db->set('amount',$payment_details[0]['amount']);
//            $this->db->set('challan_no',$payment_details[0]['challan_no']);
//            $this->db->set('dead_line',$payment_details[0]['dead_line']);
//            $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
//            $this->db->set('scan_challan',$scan_challan);
//            $this->db->set('fine_application',$payment_details[0]['fine_application']);
//            $this->db->set('actual_amount',$payment_details[0]['actual_amount']);
//            $this->db->set('paid',$payment_details[0]['paid']);
//            $this->db->set('student_id',$payment_details[0]['student_id']);
//            $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
//            $this->db->set('contract_id',$payment_details[0]['contract_id']);
//            $this->db->set('paid_date',$payment_details[0]['paid_date']);
//            $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
//            $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
//            $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
//            $this->db->set('college_fee',$payment_details[0]['college_fee']);
//            $this->db->set('clear_college_fee',$payment_details[0]['clear_college_fee']);
//            $this->db->set('add_by',$payment_details[0]['add_by']);
//            $this->db->set('last_edit',$this->session->userdata['name']);
//            $this->db->set('reason',$reason);
//            $this->db->set('fee_pay_through',$payment_details[0]['fee_pay_through']);
//            $this->db->set('bank_details',$payment_details[0]['bank_details']);
//            $this->db->set('tid_no',$payment_details[0]['tid_no']);
//            $this->db->set('submitted_fee_campus_id',$payment_details[0]['submitted_fee_campus_id']);
//            $this->db->set('book_no',$payment_details[0]['book_no']);
//            $this->db->set('receipt_no',$payment_details[0]['receipt_no']);
//            $this->db->set('ok_by_admin',1);
//            $this->db->set('clear_by','System');
//
//            $this->db->insert('update_payment_requests');
//        }
//
//        if($update_type=='extend_date')
//        {
//            $new_dead_line = $this->input->post('new_dead_line');
//            $reason = $this->input->post('reason');
//
//            $date1=date_create($new_dead_line);
//            $date2=date_create($payment_details[0]['dead_line']);
//            $diff=date_diff($date1,$date2)->days;
//
//            $already_requested = $this->db->get_where('update_payment_requests',array('id'=>$payment_details[0]['id']))->result_array();
//
//            if($diff<32 && count($already_requested)<1)
//            {
//                //UPDATE EXTEND DATE IN PAYMENTS
//                $this->db->set('dead_line',$new_dead_line);
//                $this->db->set('clear_college_fee',0);
//                $this->db->set('clear_by','');
//                $this->db->where('id',$payment_details[0]['id']);
//                $this->db->update('payments');
//
//                //ADD RECORD IN UPDATE PAYMENT REQUEST TABLE
//                $this->db->set('id',$payment_details[0]['id']);
//                $this->db->set('amount',$payment_details[0]['amount']);
//                $this->db->set('challan_no',$payment_details[0]['challan_no']);
//                $this->db->set('dead_line',$new_dead_line);
//                $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
//                $this->db->set('scan_challan',$payment_details[0]['scan_challan']);
//                $this->db->set('fine_application',$payment_details[0]['fine_application']);
//                $this->db->set('actual_amount',$payment_details[0]['actual_amount']);
//                $this->db->set('paid',$payment_details[0]['paid']);
//                $this->db->set('student_id',$payment_details[0]['student_id']);
//                $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
//                $this->db->set('contract_id',$payment_details[0]['contract_id']);
//                $this->db->set('paid_date',$payment_details[0]['paid_date']);
//                $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
//                $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
//                $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
//                $this->db->set('college_fee',$payment_details[0]['college_fee']);
//                $this->db->set('clear_college_fee',$payment_details[0]['clear_college_fee']);
//                $this->db->set('add_by',$payment_details[0]['add_by']);
//                $this->db->set('last_edit',$this->session->userdata['name']);
//                $this->db->set('reason',$reason);
//                $this->db->set('fee_pay_through',$payment_details[0]['fee_pay_through']);
//                $this->db->set('bank_details',$payment_details[0]['bank_details']);
//                $this->db->set('tid_no',$payment_details[0]['tid_no']);
//                $this->db->set('submitted_fee_campus_id',$payment_details[0]['submitted_fee_campus_id']);
//                $this->db->set('book_no',$payment_details[0]['book_no']);
//                $this->db->set('receipt_no',$payment_details[0]['receipt_no']);
//                $this->db->set('ok_by_admin',1);
//                $this->db->set('clear_by','System');
//
//                $this->db->insert('update_payment_requests');
//            }
//            else
//            {
//                $this->db->set('id',$payment_details[0]['id']);
//                $this->db->set('amount',$payment_details[0]['amount']);
//                $this->db->set('challan_no',$payment_details[0]['challan_no']);
//                $this->db->set('dead_line',$new_dead_line);
//                $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
//                $this->db->set('scan_challan',$payment_details[0]['scan_challan']);
//                $this->db->set('fine_application',$payment_details[0]['fine_application']);
//                $this->db->set('actual_amount',$payment_details[0]['actual_amount']);
//                $this->db->set('paid',$payment_details[0]['paid']);
//                $this->db->set('student_id',$payment_details[0]['student_id']);
//                $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
//                $this->db->set('contract_id',$payment_details[0]['contract_id']);
//                $this->db->set('paid_date',$payment_details[0]['paid_date']);
//                $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
//                $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
//                $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
//                $this->db->set('college_fee',$payment_details[0]['college_fee']);
//                $this->db->set('clear_college_fee',$payment_details[0]['clear_college_fee']);
//                $this->db->set('add_by',$payment_details[0]['add_by']);
//                $this->db->set('last_edit',$this->session->userdata['name']);
//                $this->db->set('reason',$reason);
//                $this->db->set('fee_pay_through',$payment_details[0]['fee_pay_through']);
//                $this->db->set('bank_details',$payment_details[0]['bank_details']);
//                $this->db->set('tid_no',$payment_details[0]['tid_no']);
//                $this->db->set('submitted_fee_campus_id',$payment_details[0]['submitted_fee_campus_id']);
//                $this->db->set('book_no',$payment_details[0]['book_no']);
//                $this->db->set('receipt_no',$payment_details[0]['receipt_no']);
//
//                $this->db->insert('update_payment_requests');
//            }
//        }
//
//        if($update_type=='delete_payment')
//        {
//            $reason = $this->input->post('reason');
//
//            $this->db->set('id',$payment_details[0]['id']);
//            $this->db->set('amount',$payment_details[0]['amount']);
//            $this->db->set('challan_no',$payment_details[0]['challan_no']);
//            $this->db->set('dead_line',$payment_details[0]['dead_line']);
//            $this->db->set('old_dead_line',$payment_details[0]['dead_line']);
//            $this->db->set('scan_challan',$payment_details[0]['scan_challan']);
//            $this->db->set('fine_application',$payment_details[0]['fine_application']);
//            $this->db->set('actual_amount',$payment_details[0]['actual_amount']);
//            $this->db->set('paid',$payment_details[0]['paid']);
//            $this->db->set('student_id',$payment_details[0]['student_id']);
//            $this->db->set('contractor_id',$payment_details[0]['contractor_id']);
//            $this->db->set('contract_id',$payment_details[0]['contract_id']);
//            $this->db->set('paid_date',$payment_details[0]['paid_date']);
//            $this->db->set('payment_plan',$payment_details[0]['payment_plan']);
//            $this->db->set('remaining_installment_amount',$payment_details[0]['remaining_installment_amount']);
//            $this->db->set('extra_amount',$payment_details[0]['extra_amount']);
//            $this->db->set('college_fee',$payment_details[0]['college_fee']);
//            $this->db->set('clear_college_fee',$payment_details[0]['clear_college_fee']);
//            $this->db->set('add_by',$payment_details[0]['add_by']);
//            $this->db->set('last_edit',$this->session->userdata['name']);
//            $this->db->set('reason',$reason);
//            $this->db->set('fee_pay_through',$payment_details[0]['fee_pay_through']);
//            $this->db->set('bank_details',$payment_details[0]['bank_details']);
//            $this->db->set('tid_no',$payment_details[0]['tid_no']);
//            $this->db->set('submitted_fee_campus_id',$payment_details[0]['submitted_fee_campus_id']);
//            $this->db->set('book_no',$payment_details[0]['book_no']);
//            $this->db->set('receipt_no',$payment_details[0]['receipt_no']);
//            $this->db->set('del',1);
//
//            $this->db->insert('update_payment_requests');
//        }
//
//        if($update_type=='payment_discount')
//        {
//
//            $this->db->select('*');
//            $this->db->from('discounts_approval');
//            $this->db->where("student_id = '".$payment_id."' and status='0'");
//            $checkRequest = $this->db->get()->result_array();
//
//            if(count($checkRequest)>0)
//            {
//
//                print_r($checkRequest);
//                exit();
//                $this->session->set_flashdata('error', 'Fee Discount Request is already been submitted. Kindly clear your previous request first.');
//
//                redirect(site_url().'/students/payments_paid/'.$payment_id);
//
//            }
//
//
//            //load the helper
//            $this->load->helper('form');
//
//            //Configure
//            //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
//            $config['upload_path'] = 'uploads/';
//
//            // set the filter image types
//            $config['allowed_types'] = 'gif|jpg|png';
//
//            //load the upload library
//            $this->load->library('upload', $config);
//
//            $this->upload->initialize($config);
//
//            $this->upload->set_allowed_types('*');
//
//            $data['upload_data'] = '';
//
//            //if not successful, set the error message
//            if (!$this->upload->do_upload('image')) {
//                $data = array('msg' => $this->upload->display_errors());
//                $image = '';
//
//            }
//            else
//            {
//                //else, set the success message
//                $data['upload_data'] = $this->upload->data();
//                if($data['upload_data']['file_name']){
//                    $image = $data['upload_data']['file_name'];
//                }
//            }
//
//
//
//            $disc = $this->input->post('apldiscount');
//            $reasondisc = $this->input->post('reason_disc');
//            $rem = $this->input->post('remainfee');
//
//
//            $this->db->set('student_id',$payment_id);
//            $this->db->set('remaining_fee',$rem);
//            $this->db->set('reason',$reasondisc);
//            $this->db->set('discount',$disc);
//            $this->db->set('application',$image);
//            $this->db->set('created_by',$this->session->userdata('name'));
//
//            $this->db->insert('discounts_approval');
//            redirect(site_url().'/students/payments_paid/'.$payment_id);
//        }
//
//        if($payment_details[0]['contract_id']>0)
//        {
//            $this->session->set_flashdata('message', 'Payment request submitted successfully');
//            redirect(site_url().'/contractors/contract_payments_paid/'.$this->input->post('contract_id'));
//        }
//        else
//        {
//            $this->session->set_flashdata('message', 'Payment request submitted successfully');
//            redirect(site_url().'/students/payments_paid/'.$payment_details[0]['student_id']);
//        }
//    }

    public function add_extra_consulation_fee($id)
    {
        $dead_line = $this->input->post('consulation_dead_line_1');
        $challan_no = $this->getChallanNo();

        $this->db->set('amount', $this->input->post('consulation_fee_1'));
        $this->db->set('dead_line', $dead_line);
        $this->db->set('student_id', $id);
        $this->db->set('payment_plan', 'consulation fee');
        $this->db->set('payment_comment', 'This fee for next exam # '.$this->input->post('exam_no').' '.$this->input->post('class'));
        $this->db->set('challan_no', $challan_no);
        $this->db->set('add_by', $this->session->userdata('name'));
        $this->db->set('last_edit', $this->session->userdata('name'));
        $this->db->insert('payments');

        $this->session->set_flashdata('message', 'Extra consultaion fee added successfully.');
        redirect(site_url().'/students/payments_paid/'.$id);
    }

    public function add_extra_fee($id)
    {
        $fee_type = $this->input->post('fee_type');

        if($fee_type=='College Fee')
        {
            $dead_line = $this->input->post('extra_fee_dead_line');
            $challan_no = $this->getChallanNo();

            $this->db->set('amount', $this->input->post('extra_fee'));
            $this->db->set('dead_line', $dead_line);
            $this->db->set('student_id', $id);
            $this->db->set('payment_plan', 'Custom Plan');
            $this->db->set('payment_comment', $this->input->post('payment_comment'));
            $this->db->set('challan_no', @$challan_no);
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->set('last_edit', $this->session->userdata('name'));
            $this->db->insert('payments');
        }

        if($fee_type=='Extra Fee')
        {
            $dead_line = $this->input->post('extra_fee_dead_line');
            $challan_no = $this->getChallanNo();

            $this->db->set('amount', $this->input->post('extra_fee'));
            $this->db->set('dead_line', $dead_line);
            $this->db->set('student_id', $id);
            $this->db->set('payment_plan', 'Custom Plan');
            if($this->input->post('fee_for')=='Other')
            {
                $this->db->set('payment_comment', $this->input->post('payment_comment').' For '.$this->input->post('payment_for'));
            }
            else
            {
                $this->db->set('payment_comment', $this->input->post('payment_comment').' For '.$this->input->post('fee_for'));
            }
            $this->db->set('challan_no', $challan_no);
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->set('last_edit', $this->session->userdata('name'));
            $this->db->insert('payments');
        }

        if($fee_type=='consulation fee')
        {
            $dead_line = $this->input->post('extra_fee_dead_line');
            $challan_no = $this->getChallanNo();

            $this->db->set('amount', $this->input->post('extra_fee'));
            $this->db->set('dead_line', $dead_line);
            $this->db->set('student_id', $id);
            $this->db->set('payment_plan', 'consulation fee');
            $this->db->set('payment_comment', 'This fee for next exam # '.$this->input->post('exam_no').' '.$this->input->post('class'));
            $this->db->set('challan_no', $challan_no);
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->set('last_edit', $this->session->userdata('name'));
            $this->db->insert('payments');
        }

        $this->session->set_flashdata('message', 'Extra fee added successfully.');
        redirect(site_url().'/students/payments_paid/'.$id);
    }
    
    public function add_council_fee($id)
    {
        $exam_sequence = $this->db->get_where("exam_sequence","id = ".$this->input->post('exam_sequence'))->row_array();
        $course = $this->db->get_where("courses","course_id = ".$exam_sequence['course_id'])->row_array();
        $type = $course['course_type'] == 'Annual' ? "Year" : $course['course_type'];
        
        $this->db->select('*');
        $this->db->from('council_sequence');
        $this->db->where('course_id', $exam_sequence['course_id']);
        $this->db->where('has_fee', 1);
        $this->db->where('action_type', 'fee');
        $this->db->where_in('recurring',['One Time', 'Each Exam', 'Every Semester']);
        $sequences = $this->db->get()->result_array();
        $today = date('Y-m-d');
        $errors = [];
        $this->db->trans_start();

        foreach($sequences as $sequence){
            
            $fee = $this->db->order_by('from_date','ASC')
                ->where('sequence_fee_id', $sequence['council_sequence_id'])
                ->where('exam_sequence_id', $exam_sequence['id'])
                ->get('council_sequence_fee_rules')
                ->row_array();
            if($fee){
                
                $already = $this->db->get_where('payments', [
                    'student_id' => $id,
                    'exam_sequence_id' => $exam_sequence['id'],
                    'exam_class' => $exam_sequence['class'],
                    'council_sequence_id' => $sequence['council_sequence_id']
                ])->row_array();
    
                if (!$already) {
                    $dead_line = $fee['from_date'];
                    $challan_no = $this->getChallanNo();
            
                    $this->db->set('amount', $fee['exam_fee']);
                    $this->db->set('disc_per_inst', 0);
                    $this->db->set('dead_line', $fee['from_date']);
                    $this->db->set('student_id', $id);
                    $this->db->set('payment_plan', 'consulation fee');
                    $this->db->set('payment_comment', 'This fee for next exam # '.$exam_sequence['first_year'].' '.$this->getOrdinal($exam_sequence['class']).' '.$type);
                    $this->db->set('challan_no', $challan_no);
                    $this->db->set('add_by',$this->session->userdata('name'));
                    $this->db->set('last_edit',$this->session->userdata('name'));
                    $this->db->set('exam_sequence_id', $exam_sequence['id']);
                    $this->db->set('council_sequence_id', $sequence['council_sequence_id']);
                    $this->db->set('exam_class', $exam_sequence['class']);
                    $this->db->insert('payments');
                }
        
        
            } else {
                $errors[] = 'Please Generate Fee For '.$sequence['type_name'];
            }
        }
        
        if(!empty($errors)){
            $this->db->trans_rollback();
        
            $this->session->set_flashdata('error', implode('<br>', $errors));
        
            redirect('students/payments_paid/'.$id);
        }
        $this->db->trans_complete();
        $this->session->set_flashdata('message', 'Council fee added successfully.');
        redirect(site_url().'/students/payments_paid/'.$id);
    }
    
    public function add_fee_installments($id)
    {
        $installment_day = $this->input->post('installment_day');
        $installment_start_month = $this->input->post('installment_start_month');
        $installment_end_month = $this->input->post('installment_end_month');
        $fee_not_created = $this->input->post('fee_not_created');
        
        
        if($fee_not_created==0)
        {
            $this->session->set_flashdata('error', 'No Installment Added. All instalments already created');
            redirect(site_url().'/students/payments_paid/'.$id);
        }
        
        $startDate = new DateTime($installment_start_month);
        $endDate = new DateTime($installment_end_month);
        
        // Array to hold the result dates
        $dates = [];
        
        // Loop through each month in the range
        while ($startDate <= $endDate) {
            // Add the 10th date of the current month to the array
            $dates[] = $startDate->format('Y-m') . '-'.$installment_day;
        
            // Move to the first day of the next month
            $startDate->modify('first day of next month');
        }
        
        $total_months = count($dates);
        
        $installment_amount = $fee_not_created/$total_months;
        
        //CHECK INSTALLMENT AMOUNT IS GREATER THAN CLASS MINIMUM INSTALLMENT AMOUNT
        $this->db->select('*');
        $this->db->from('students');
        $this->db->join('classes','classes.class_id=students.class_id','inner');
        $this->db->where('students.student_id',$id);
        $minimum_installment_fee = $this->db->get()->row()->minimum_installment_fee;
        
        if($installment_amount<$minimum_installment_fee)
        {
            $this->session->set_flashdata('error', 'Instalmments not created because minimum installment amount is set by admin is Rs.'.$minimum_installment_fee);
            redirect(site_url().'/students/payments_paid/'.$id);
        }
        
        for($i=0; $i<$total_months;$i++)
        {   
            if($total_months==($i+1))
            {
                $amount = $fee_not_created;
            }
            else
            {
                $amount = floor($installment_amount / 100) * 100;
                $fee_not_created = $fee_not_created-$amount;
            }
            
            echo $dates[$i].' = '.$amount.'(remainig fee = '.$fee_not_created.')<br />';
            
            $challan_no = $this->getChallanNo();
    
            $this->db->set('amount', $amount);
            $this->db->set('dead_line', $dates[$i]);
            $this->db->set('student_id', $id);
            $this->db->set('payment_plan', 'Custom Plan');
            $this->db->set('payment_comment', 'College Fee');
            $this->db->set('challan_no', @$challan_no);
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->set('last_edit', $this->session->userdata('name'));
            $this->db->insert('payments');
        }

        $this->session->set_flashdata('message', 'Installments added successfully.');
        redirect(site_url().'/students/payments_paid/'.$id);
    }
    
    public function check_add_fee_installments($id)
    {
        $html='';
        $installment_day = $this->input->post('installment_day');
        $installment_start_month = $this->input->post('installment_start_month');
        $installment_end_month = $this->input->post('installment_end_month');
        $fee_not_created = $this->input->post('fee_not_created');
        
        if($installment_start_month>$installment_end_month)
        {
            $html.= 'Invalid Dates Kindly Check the selected dates.';
            echo $html;
            exit();
        }
        
        
        if($fee_not_created==0)
        {
            $html.= 'There is no remaining fee.';
            echo $html;
            exit();
        }
        
        $startDate = new DateTime($installment_start_month);
        $endDate = new DateTime($installment_end_month);
        
        // Array to hold the result dates
        $dates = [];
        
        // Loop through each month in the range
        while ($startDate <= $endDate) {
            // Add the 10th date of the current month to the array
            $dates[] = $startDate->format('Y-m') . '-'.$installment_day;
        
            // Move to the first day of the next month
            $startDate->modify('first day of next month');
        }
        
        $total_months = count($dates);
        
        $installment_amount = $fee_not_created/$total_months;
        
        //CHECK INSTALLMENT AMOUNT IS GREATER THAN CLASS MINIMUM INSTALLMENT AMOUNT
        $this->db->select('*');
        $this->db->from('students');
        $this->db->join('classes','classes.class_id=students.class_id','inner');
        $this->db->where('students.student_id',$id);
        $minimum_installment_fee = $this->db->get()->row()->minimum_installment_fee;
        
        if($installment_amount<$minimum_installment_fee && $this->session->userdata('role')!='Admin')
        {
            $html.= 'Installment cannot be created. Installment amount is less than Rs.'.$minimum_installment_fee.' Kindly reduce your duration';
            echo $html;
            exit();
        }
        
        
        $html.='<table class="table table-responsive">';
        $html.='<tr><th>Date</th><th>Amount</th></tr>';
        
        for($i=0; $i<$total_months;$i++)
        {   
            if($total_months==($i+1))
            {
                $amount = $fee_not_created;
            }
            else
            {
                $amount = floor($installment_amount / 100) * 100;
                $fee_not_created = $fee_not_created-$amount;
            }
            $html.='<tr>';
            $html.='<td>'.$dates[$i].'</td>';
            $html.='<td>Rs '.$amount.'</td>';
            $html.='</tr>';
        }
        $html.='</table>';
        echo $html;
    }

    public function split_payment($student_id)
    {
        //$date1=date_create($this->input->post('new_dead_line'));
        //$date2=date_create($this->input->post('current_dead_line'));
        //$diff=date_diff($date1,$date2)->days;
        $id = $this->input->post('current_id');
        $installment = $this->db->get_where('payments',array('id'=>$id))->result_array();
        $split_count = $installment[0]['split'];
        //echo $split_count;
        //exit();
        if($split_count<1)
        {
            //CURRENT INSTALLMENT UPDATE
            $amount = $this->input->post('current_amount');
            $remaining_installment_amount = $this->input->post('current_remaining_installment_amount');
            $extra_amount = $this->input->post('current_extra_amount');
            $id = $this->input->post('current_id');

            $this->db->set('amount',$amount);
            $this->db->set('remaining_installment_amount',$remaining_installment_amount);
            $this->db->set('extra_amount',$extra_amount);
            if ($installment[0]['split_from'] != NULL) {
                $this->db->set('split', 2);
            }else
            {
                $this->db->set('split', 1);
            }
            $this->db->where('id',$id);
            $this->db->update('payments');

            //NEW INSTALLMENT CREATE
            $amount = $this->input->post('new_amount');
            $remaining_installment_amount = $this->input->post('new_remaining_installment_amount');
            $extra_amount = $this->input->post('new_extra_amount');
            $dead_line = $this->input->post('new_dead_line');

            $challan_no = $this->getChallanNo();

            $this->db->set('amount', $amount);
            $this->db->set('remaining_installment_amount', $remaining_installment_amount);
            $this->db->set('extra_amount', $extra_amount);
            $this->db->set('dead_line', $dead_line);
            $this->db->set('student_id', $student_id);
            $this->db->set('payment_plan', $installment[0]['payment_plan']);
            $this->db->set('payment_comment', $installment[0]['payment_comment']);
            $this->db->set('challan_no', $challan_no);
            if ($installment[0]['split_from'] != NULL){

                $this->db->set('split',2);
                $this->db->set('split_from', $installment[0]['split_from']);
            }else
                $this->db->set('split_from', $id);
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->set('last_edit', $this->session->userdata('name'));
            $this->db->insert('payments');


            $this->session->set_flashdata('message', 'Payment Split successfully.');
            redirect(site_url().'/students/payments_paid/'.$student_id);
        }
        else
        {
            $this->session->set_flashdata('error', 'Split Payment Failed. You can split 1 installment maximum 2 times.');
            redirect(site_url().'/students/payments_paid/'.$student_id);
        }
    }

    public function split_consulation_payment($student_id)
    {
        //$date1=date_create($this->input->post('new_dead_line'));
        //$date2=date_create($this->input->post('current_dead_line'));
        //$diff=date_diff($date1,$date2)->days;
        $id = $this->input->post('current_id');
        $installment = $this->db->get_where('payments',array('id'=>$id))->result_array();
        $split_count = $installment[0]['split'];
        //echo $split_count;
        //exit();
        if($split_count<1)
        {
            //CURRENT INSTALLMENT UPDATE
            $amount = $this->input->post('current_amount');
            $remaining_installment_amount = $this->input->post('current_remaining_installment_amount');
            $extra_amount = $this->input->post('current_extra_amount');
            $id = $this->input->post('current_id');

            $this->db->set('amount',$amount);
            $this->db->set('remaining_installment_amount',$remaining_installment_amount);
            $this->db->set('extra_amount',$extra_amount);
            if ($installment[0]['split_from'] != NULL) {
                $this->db->set('split', 2);
            }else
            {
                $this->db->set('split', 1);
            }
            $this->db->where('id',$id);
            $this->db->update('payments');

            //NEW INSTALLMENT CREATE
            $amount = $this->input->post('new_amount');
            $remaining_installment_amount = $this->input->post('new_remaining_installment_amount');
            $extra_amount = $this->input->post('new_extra_amount');
            $dead_line = $this->input->post('new_dead_line');

            $challan_no = $this->getChallanNo();

            $payment_comment = 'Remaining Consulation fee of exam #'.str_replace('This fee for next exam #','',$installment[0]['payment_comment']);

            $this->db->set('amount', $amount);
            $this->db->set('remaining_installment_amount', $remaining_installment_amount);
            $this->db->set('extra_amount', $extra_amount);
            $this->db->set('dead_line', $dead_line);
            $this->db->set('student_id', $student_id);
            $this->db->set('payment_plan', 'Custom Plan');
            $this->db->set('payment_comment', $payment_comment);
            $this->db->set('challan_no', $challan_no);
            if ($installment[0]['split_from'] != NULL){

                $this->db->set('split',2);
                $this->db->set('split_from', $installment[0]['split_from']);
            }else
                $this->db->set('split_from', $id);
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->set('last_edit', $this->session->userdata('name'));
            $this->db->insert('payments');


            $this->session->set_flashdata('message', 'Payment Split successfully.');
            redirect(site_url().'/students/payments_paid/'.$student_id);
        }
        else
        {
            $this->session->set_flashdata('error', 'Split Payment Failed. You can split 1 installment maximum 2 times.');
            redirect(site_url().'/students/payments_paid/'.$student_id);
        }
    }

    public function reset_plan($student_id)
    {
        $this->db->where('student_id', $student_id);
        $this->db->delete('payments');
        $this->session->set_flashdata('message', 'Student Plan has been reset successfully');
        redirect('students/all_students');
    }

    public function sms($id)
    {
        $incoming_messages = array();
        $student = $this->student->editStudent($id);
        $mobile = $student[0]['mobile'];
        $mobile = '+92'.substr($mobile, -10);
        $emergency_number = $student[0]['emergency_no'];
        $emergency_number = '+92'.substr($emergency_number, -10);

        //GET DEVICE AND TOKEN
        $this->db->select('*');
        $this->db->from('students');
        $this->db->join('classes','classes.class_id=students.class_id','inner');
        $this->db->join('campuses','classes.campus_id=campuses.campus_id','inner');
        $this->db->join('sms_gateway','sms_gateway.campus_id=campuses.campus_id','inner');
        $this->db->where('students.student_id',$id);
        $device_details = $this->db->get()->result_array();

        $url= 'https://semysms.net/api/3/inbox_sms.php';

        $data = array(
            "start_id" => 1,
            "end_id" => 1000000000,
            "phone" => $mobile,
            "device" => $device_details[0]['device_id'],
            "token" => $device_details[0]['token']
        );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($curl);
        curl_close($curl);

        $result_array1 =  json_decode($output,true);
        /*echo '<pre>';
        print_r($result_array1);
        echo '</pre>';
        exit();*/
        if(count($result_array1)>0)
        {
            foreach(@$result_array1['data'] as $data)
            {
                array_push($incoming_messages,$data);
            }
        }


        $data = array(
            "start_id" => 1,
            "end_id" => 1000000000,
            "phone" => $emergency_number,
            "device" => $device_details[0]['device_id'],
            "token" => $device_details[0]['token']
        );

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $output = curl_exec($curl);
        curl_close($curl);


        $result_array2 =  json_decode($output,true);
        foreach($result_array2['data'] as $data)
        {
            array_push($incoming_messages,$data);
        }


        //$data['smss'] = $this->student->getSms($mobile);
        $mobile = $student[0]['mobile'];
        $emergency_number = $student[0]['emergency_no'];
        $this->db->select('*');
        $this->db->from('sms');
        $this->db->or_where(array('number'=>$mobile));
        $this->db->or_where(array('number'=>$emergency_number));
        $smss = $this->db->get()->result_array();


        foreach($smss as $sms)
        {
            array_push($incoming_messages,$sms);
        }

        function date_compare($a, $b)
        {
            $t1 = strtotime($a['date']);
            $t2 = strtotime($b['date']);
            return $t1 - $t2;
        }
        usort($incoming_messages, 'date_compare');

        $data['smss'] = $incoming_messages;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/sms', $data);
        $this->load->view('inc/footer');
    }

    public function send_sms($student_id)
    {
        $student = $this->db->get_where('students', array('student_id'=>$student_id))->result_array();
        $message = $this->input->post('message');
        for($i=1;$i<=2;$i++){
            if($i==1){
                $this->db->set('number', $student[0]['mobile']);
                $this->db->set('message', $message);
                $this->db->set('status', '');
                $this->db->set('date', date('Y-m-d H:i:s'));
                $this->db->set('chk', '0');
                $this->db->insert('sms');
            }
            else
            {
                $this->db->set('number', $student[0]['emergency_no']);
                $this->db->set('message', $message);
                $this->db->set('status', '');
                $this->db->set('date', date('Y-m-d H:i:s'));
                $this->db->set('chk', '0');
                $this->db->insert('sms');
            }
        }
        $this->session->set_flashdata('message', 'Message send successfully');
        redirect(site_url().'/students/sms/'.$student_id);
    }

    public function feeSmsAlertToStudent($student_id, $paid_date, $actual_amount)
    {
        $student = $this->db->get_where('students', array('student_id'=>$student_id))->result_array();
        $message = 'Dear '.$student[0]['first_name'].' '.$student[0]['last_name'].'

        You have successfully submitted your fee Rs '.$actual_amount.' on '.$paid_date.'. You can confirm your fee status from college website.
        
        From
        Pharmacy Group of Colleges
        ';
        $numbers = array();
        array_push($numbers, $student[0]['mobile']);
        array_push($numbers, $student[0]['emergency_no']);

        foreach($numbers as $number)
        {
            $this->db->set('number', $number);
            $this->db->set('message', $message);
            $this->db->set('status', '');
            $this->db->set('date', date('Y-m-d H:i:s'));
            $this->db->set('chk', '0');
            $this->db->set('add_by', 'System');
            $this->db->insert('sms');
        }
    }

    public function feeSmsAlertToControlCenter($student_id, $paid_date, $actual_amount, $type, $add_by)
    {
        $student = $this->db->get_where('students', array('student_id'=>$student_id))->result_array();
        $class = $this->db->get_where('classes', array('class_id'=>$student[0]['class_id']))->result_array();

        if($type==1)
        {
            $type = 'College Fee';
        }
        else
        {
            $type = 'Bank Fee';
        }

        $message = 'Fee Submission Alert
        Student Name : '.$student[0]['first_name'].' '.$student[0]['last_name'].'
        Roll No : '.$student[0]['roll_no'].'
        Paid Date : '.$paid_date.'
        Amount : '.$actual_amount.'
        Type : '.$type.'
        Add By : '.$add_by.'
        
        From
        Pharmacy Group of Colleges
        ';
        $numbers = array();
        array_push($numbers, $class[0]['phone1']);
        array_push($numbers, $class[0]['phone2']);

        foreach($numbers as $number)
        {
            $this->db->set('number', $number);
            $this->db->set('message', $message);
            $this->db->set('status', '');
            $this->db->set('date', date('Y-m-d H:i:s'));
            $this->db->set('chk', '0');
            $this->db->set('add_by', 'System');
            $this->db->insert('sms');
        }
    }

    public function student_card_front($student_id)
    {
        $this->db->select('students.*, classes.campus_id, classes.course_id');
        $this->db->from('students');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->where('students.student_id', $student_id);
        $data['students'] = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('student_documents');
        $this->db->where(array('student_id'=>$student_id, 'type'=>'Photo'));
        $data['photo'] = $this->db->get()->result_array();

        $data['campus'] = $this->db->get_where('campuses', array('campus_id'=>$data['students'][0]['campus_id']))->result_array();

        $this->load->view('students/student_card', $data);
    }

    public function student_card_back($student_id)
    {
        $this->db->select('students.*, classes.campus_id, classes.session');
        $this->db->from('students');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->where('students.student_id', $student_id);
        $data['students'] = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('student_documents');
        $this->db->where(array('student_id'=>$student_id, 'type'=>'Photo'));
        $data['photo'] = $this->db->get()->result_array();

        $data['campus'] = $this->db->get_where('campuses', array('campus_id'=>$data['students'][0]['campus_id']))->result_array();

        $this->load->view('students/student_card_back', $data);
    }

    public function getCampusClasses()
    {
        $campus_id = $this->input->post('campus_id');
        $course_id = $this->input->post('course_id');
        if($course_id != '' && $course_id != Null)
            $classes = $this->db->get_where('classes', array('course_id'=>$course_id,'campus_id'=>$campus_id,'dead_line_entry>='=>date('Y-m-d'),'status'=>1))->result_array();
        elseif($campus_id != '')
            $classes = $this->db->get_where('classes', array('campus_id'=>$campus_id,'dead_line_entry>='=>date('Y-m-d'),'status'=>1))->result_array();
        $html='';
        $html.='<option value="">SELECT CLASS</option>';
        foreach($classes as $class)
        {
            $html.= '<option value="'.$class['class_id'].'" data-exam-no="'.$class['exam_no'].'">'.$class['name'].'</option>';
        }
        echo $html;
        exit;
    }
    
    public function getStudyTypes()
    {
        $course_id = $this->input->post('course_id');
        
        $study_types = $this->db->get_where('study_type',array('course_id'=>$course_id))->result_array(); 
        
        $html='';
        $html.='<option value="">SELECT STUDY TYPE</option>';
        foreach($study_types as $study_type)
        {
            $html.= '<option value="'.$study_type['id'].'">'.$study_type['name'].'</option>';
        }
        echo $html;
        exit;
    }

    public function getCampusClass()
    {
        $campus_id = $this->input->post('campus_id');
        $course_id = $this->input->post('course_id');
        if($course_id != '' && $course_id != Null)
            $classes = $this->db->get_where('classes', array('course_id'=>$course_id,'campus_id'=>$campus_id,'status'=>1))->result_array();
        elseif($campus_id != '')
            $classes = $this->db->get_where('classes', array('campus_id'=>$campus_id,'status'=>1))->result_array();
        $html='';
        $html.='<option value="">SELECT CLASS</option>';
        foreach($classes as $class)
        {
            $html.= '<option value="'.$class['class_id'].'" data-exam-no="'.$class['exam_no'].'">'.$class['name'].'</option>';
        }
        echo $html;
        exit;
    }

    public function getCampusCourses()
    {
        $campus_id = $this->input->post('campus_id');
        $cnic = $this->input->post('cnic');
        $enroll_courses = $this->db->join('classes',"classes.class_id = students.class_id")->get_where("students","cnic = '$cnic'")->result_array();
        $courses = array_column($enroll_courses, 'course_id');
        if (count($courses)<1)
            $classes = $this->db->get_where('courses', array('campus_ids like'=>'%'.$campus_id.'%'))->result_array();
        else
            $classes = $this->db->where_not_in("course_id",$courses)->get_where('courses', array('campus_ids like'=>'%'.$campus_id.'%'))->result_array();
        $html='';
        $html.='<option value="">SELECT COURSE</option>';
        foreach($classes as $class)
        {
            $html.= '<option value="'.$class['course_id'].'">'.$class['course_name'].'</option>';
        }
        echo $html;
        exit;
    }

    public function getAllCampusCourses()
    {
        $courses = $this->db->get('courses')->result_array();
        $html='';
        $html.='<option value="">SELECT COURSE</option>';
        foreach($courses as $course)
        {
            $html.= '<option value="'.$course['course_id'].'">'.$course['course_name'].'</option>';
        }
        echo $html;
        exit;
    }

    public function getCampusShifts()
    {
        $campus_id = $this->input->post('campus_id');
        $this->db->where("find_in_set($campus_id, campus_id)");
        $classes = $this->db->get('shifts')->result_array();
        $html='';
        $html.='<option value="">SELECT Shift</option>';
        foreach($classes as $class)
        {
            $html.= '<option value="'.$class['id'].'">'.$class['name'].'</option>';
        }
        echo $html;
        exit;
    }

    public function updateStudentsClass()
    {
        $students = $this->db->get('students')->result_array();

        foreach($students as $student)
        {
            $this->db->select('*');
            $this->db->from('punjab_council_roll_number');
            $this->db->where('cnic', $student['cnic']);
            $results = $this->db->get()->result_array();

            foreach($results as $result)
            {
                if($result['class']==1)
                {
                    if($result['result_remarks']=='Pass')
                    {
                        $this->db->set('section','Second Year');
                        $this->db->where('student_id',$student['student_id']);
                        $this->db->update('students');
                    }
                    elseif($result['result_remarks']=='Pass*')
                    {
                        $this->db->set('section','Second Year');
                        $this->db->where('student_id',$student['student_id']);
                        $this->db->update('students');
                    }
                }
            }
        }
    }

    public function getFine()
    {
        $dead_line = $this->input->post('dead_line');
        $paid_date = $this->input->post('paid_date');

        $dead_line = date_create($dead_line);
        $paid_date = date_create($paid_date);
        $diff=date_diff($dead_line,$paid_date);
        $difference = $diff->format("%R%a");

        if($difference>0)
        {
            $fee_fine = $difference*50;
        }
        else
        {
            $fee_fine = 0;
        }
        echo $fee_fine;
    }

    public function verify_fee()
    {
        $tid=$this->input->post('tid');
        $bank=$this->input->post('bank');
        $date=$this->input->post('paid_date');
        $amount=$this->input->post('amount');

        $tid=str_replace(' ', '', $tid);
        $tid=preg_replace('/\s+/', '', $tid);
        $tid=trim($tid);

        $acountid = $this->db->get_where('accounts',array('account_name'=>$bank))->row()->id;
        //NEW METHOD
        //$qry = "SELECT * FROM bank_reconciliation_statement WHERE trans_date='".$date."' AND description LIKE '%".$tid."%' AND CAST(REPLACE(credit,',','') as SIGNED)='".$amount."' LIMIT 1";
        $qry = "SELECT * FROM bank_reconciliation_statement WHERE account_id= '".$acountid."' AND trans_date='".$date."' AND description LIKE '%".$tid."%' LIMIT 1";
        $transaction = $this->db->query($qry)->result_array();

        if(count($transaction)>0)
        {
            //CHECK TAGGED AMOUNT
            $amount_tagged = $transaction[0]['tagged_amount'];
            $credit_amount = str_replace(',','',str_replace('.00','',$transaction[0]['credit']));

            if(($credit_amount-$amount_tagged)>=$amount)
            {
                echo 'success';
            }
            else
            {
                $device_details = $this->db->get_where('payments',array('statement_id'=>@$transaction[0]['id']))->result_array();
                echo ' Already Found ' . @$device_details[0]['challan_no'] . '<br />' . '<a href="' . site_url() . '/students/payments_paid/' . @$device_details[0]['student_id'] . '" target="_blank" class="btn red"></i> See Data</a> <br />';
            }
        }
        else
        {
            echo 'Not Found in Statement'.'<br />'.
                    '<a href="'.site_url().'/accounts/agent_statement/'.$date.'/'.$acountid.'" target="_blank" class="btn red"></i> See Statement</a> <br />';
        }

        //GET DEVICE AND TOKEN
        // $this->db->select('*');
        // $this->db->from('payments');
        // $this->db->where('bank_details like "%'.$bank.'%" and tid_no like "%'.$tid.'%" and paid_date="'.$date.'"');
        // $device_details = $this->db->get()->result_array();
        

        // if (count($device_details) < 1)
        // {
        //     $this->db->select('*');
        //     $this->db->from('bank_reconciliation_statement');
        //     $this->db->where('account_id = "'.$acountid.'" and trans_date = "'.$date.'" and (description like "%'.$tid.'%" or reference_no like "%'.$tid.'%") and CAST(REPLACE(credit,",","") as SIGNED) >= '.$amount);
        //     $concile_details = $this->db->get()->result_array();

        //     if (count($concile_details) > 0)  
        //     {
        //         echo 'success';
        //     }
        //     else
        //     {
        //         echo 'Not Found in Statement'.'<br />'.
        //             '<a href="'.site_url().'/accounts/agent_statement/'.$date.'/'.$acountid.'" target="_blank" class="btn red"></i> See Statement</a> <br />';
        //     }
        // }
        // else
        // {
        //     $qry = "SELECT CAST(REPLACE(credit, ',', '') as SIGNED) as total_amount, `tagged_amount` FROM `bank_reconciliation_statement` WHERE `id` = '".$device_details[0]['statement_id']."'";
        //     $tagged_entry = $this->db->query($qry)->row();

        //     if ($tagged_entry != null) {
        //         if (($tagged_entry->total_amount - $tagged_entry->tagged_amount) >= $amount) 
        //         {
        //             echo 'success';
        //         } 
        //         else
        //         {
        //             echo ($tagged_entry->total_amount - $tagged_entry->tagged_amount) . ' Already Found ' . $device_details[0]['challan_no'] . '<br />' . '<a href="' . site_url() . '/students/payments_paid/' . $device_details[0]['student_id'] . '" target="_blank" class="btn red"></i> See Data</a> <br />';
        //         }
        //     }
        //     else
        //     {
        //         echo 'Not Found in Statement'.'<br />'.
        //             '<a href="'.site_url().'/accounts/agent_statement/'.$date.'/'.$acountid.'" target="_blank" class="btn red"></i> See Statement</a> <br />';
        //     }
        // }
    }

    public function search()
    {

        $access = checkUserAccess();
        $class_ids = @explode(',',$access[0]['class_ids']);
        if($access&&$access[0]['campus_ids']!=NULL)
        {
            $campuses_ids = @explode(',',$access[0]['campus_ids']);
        }
        else
        {
            $campuses_ids = array();
        }
        $val = @$this->input->post('search');


        if($this->input->post('fee')=='Check Fee')
        {
            $this->db->select('students.*, classes.name as class_name,classes.exam_no as student_exam');
            $this->db->from('students');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->where("(students.roll_no LIKE '%".$val."%' OR students.cnic LIKE '%".$val."%' OR students.mobile LIKE '%".$val."%' OR students.emergency_no LIKE '%".$val."%' OR students.first_name LIKE '%".$val."%' OR students.last_name LIKE '%".$val."%' OR students.father_name LIKE '%".$val."%')", NULL, FALSE);

            $data1 = $this->db->get()->result_array();

            $this->db->select('students.*, classes.name as class_name,classes.exam_no as student_exam');
            $this->db->from('students');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('reference_users', 'reference_users.reference_user_id=students.reference_user_id', 'left');
            $this->db->where("(reference_users.phone LIKE '%".$val."%' OR reference_users.name LIKE '%".$val."%')", NULL, FALSE);

            $data2 = $this->db->get()->result_array();

            $data['students'] = array_unique(array_merge($data1,$data2), SORT_REGULAR);

            if(count($data['students'])>0)
            {
                redirect(site_url().'/students/payments_paid/'.$data['students'][0]['student_id']);
            }
            else
            {
                $student_id = $this->db->get_where('payments', array('challan_no'=>$val))->row()->student_id;
                if($student_id!='')
                {
                    redirect(site_url().'/students/payments_paid/'.$student_id);
                }
                else
                {
                    $this->session->set_flashdata('error', 'Nothing Match.');
                    redirect('dashboard');
                }
            }
        }
        else{


            $this->db->select('students.*, classes.name as class_name,classes.exam_no as student_exam,machine_data.machine_id,courses.course_name,courses.course_id');
            $this->db->from('students');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('courses', 'classes.course_id=courses.course_id', 'inner');
            $this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
            //$this->db->like('students.roll_no', $val);
            //$this->db->like('students.cnic', $val);
            //$this->db->where(array('students.status'=>1));
            $this->db->where("(students.roll_no LIKE '%".$val."%' OR students.cnic LIKE '%".$val."%' OR students.mobile LIKE '%".$val."%' OR students.emergency_no LIKE '%".$val."%' OR students.first_name LIKE '%".$val."%' OR students.last_name LIKE '%".$val."%' OR students.father_name LIKE '%".$val."%')", NULL, FALSE);

            if($this->session->userdata('role')!='Admin'){
                $this->db->where_in('classes.class_id', $class_ids);
            }

            $data1 = $this->db->get()->result_array();

            $this->db->select('students.*, classes.name as class_name,classes.exam_no as student_exam,machine_data.machine_id,courses.course_name,courses.course_id');
            $this->db->from('students');
            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('courses', 'classes.course_id=courses.course_id', 'inner');
            $this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
            $this->db->join('reference_users', 'reference_users.reference_user_id=students.reference_user_id', 'left');
            $this->db->where("(reference_users.phone LIKE '%".$val."%' OR reference_users.name LIKE '%".$val."%')", NULL, FALSE);

            $data2 = $this->db->get()->result_array();

            $data['students'] = array_unique(array_merge($data1,$data2), SORT_REGULAR);

            $this->load->view('inc/header');
            $this->load->view('inc/sidebar');
            $this->load->view('students/anyquery_student', $data);
            $this->load->view('inc/footer');



        }
    }

    public function store_Student($data,$student_id)
    {
        foreach(@$data as $k=>$value){
            $this->db->set(''.$k.'', $value);
        }
        $this->db->where('student_id',$student_id);
        $this->db->update('students');
    }

    public function payments_paid_old($id)
    {
        $payment_id = @$this->input->post("old_plan");
        $qry = "SELECT DISTINCT `merged_challan`, discount as discount FROM `archive_payments` WHERE student_id=$id and payment_id = $payment_id and merged_challan is not null GROUP by merged_challan UNION ALL SELECT DISTINCT `merged_challan`, sum(discount) as discount FROM `archive_payments` WHERE student_id=$id and payment_id = $payment_id and merged_challan is null";
        $query = $this->db->query($qry)->result_array();

        if (count($query)>0) {
            $tt=0;
            foreach($query as $discs){
                $tt+= $discs['discount'];
            }
            $data['discountfee']=$tt;
        }

        else{
            $data['discountfee'] = '0';
        }

        $data['payments'] = $this->student->payment_paid_old($id,$payment_id);
        $data['student'] = $this->student->getSingleStudent($id);
        $data['discount'] = $this->student->getStudentDiscount_old($id,$payment_id);
        $data['paid_fee'] = $this->student->getStudentPaidFee_old($id,$payment_id);
        $data['remaining_fee'] = $this->student->getStudentRemainingFee_old($id,$payment_id);
        $data['fee_should_pay'] = $this->student->getStudentFeeShouldPay_old($id,$payment_id);
        $data['consulation_fee'] = $this->student->getStudentConsulationFee_old($id,$payment_id);
        $data['consulation_fee_should_pay'] = $this->student->getStudentConsulationFeeShouldPay_old($id,$payment_id);
        $data['consulation_fee_paid'] = $this->student->getStudentConsulationFeePaid_old($id,$payment_id);
        $data['consulation_fee_unpaid'] = $this->student->getStudentConsulationFeeUnPaid_old($id,$payment_id);
        $data['total_fine'] = $this->student->getStudentTotalFine_old($id,$payment_id);
        $data['removed_fine'] = $this->student->getStudentRemovedFine_old($id,$payment_id);
        $data['fine_should_pay'] = $this->student->getStudentFineShouldPay_old($id,$payment_id);
        $data['fine_paid'] = $this->student->getStudentFinePaid_old($id,$payment_id);
        $data['total_calls'] = $this->student->getStudentTotalCalls_old($id,$payment_id);
        $data['total_extra_fee'] = $this->student->getStudentTotalExtraFee_old($id,$payment_id);
        $data['total_extra_paid_fee'] = $this->student->getStudentTotalExtraPaidFee_old($id,$payment_id);
        $data['extra_fee_paid_till_date'] = $this->student->getStudentExtraFeePaidTillDate_old($id,$payment_id);
        $data['extra_fee_remaining_till_date'] = $this->student->getStudentExtraFeeRemainingTillDate_old($id,$payment_id);
        $data['shift_delete_fee'] = $this->student->getStudentShiftDeleteFee_old($id,$payment_id);
        $data['account_numbers'] = $this->db->get_where('accounts',array('type'=>'1'))->result_array();

        $this->db->select('campuses.*');
        $this->db->from('campus_rules');
        $this->db->join('campuses','campuses.campus_id=campus_rules.campus_id','inner');
        $this->db->join('closing_persons','campuses.campus_id=closing_persons.campus_id','inner');
        $this->db->where('campus_rules.college_fee',1);
        $this->db->where('closing_persons.active_status',1);
        $data['campuses'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/payments_paid_old', $data);
        $this->load->view('inc/footer');
    }

    public function mark_attendance()
    {
        $students = $this->input->post('student_ids');
        $date = $this->input->post('attendance_date');
        $time = $this->input->post('attendance_time');
        $date = date('Y-m-d',strtotime($date));
        $time = date('H:i:s',strtotime($time));
        $attendence_time = $date.' '.$time;
        $at_campus = $this->input->post('campus_id');
        $at_campus =$this->db->get_where('campuses',array('campus_id'=>$at_campus))->row()->campus_code;

        $students = @explode(",",$students);
        foreach($students as $machine_user_id)
        {
            $machine_user_id = @$this->db->get_where('machine_data',array('teacher_student_id'=>$machine_user_id))->row()->machine_id;

            $this->db->set('time',$attendence_time);
            $this->db->set('machine_user_id',$machine_user_id);
            $this->db->set('campus_code',$at_campus);
            $this->db->set('created_by',$this->session->userdata('name'));
            $this->db->insert('attendence');
        }
        $this->session->set_flashdata('message', 'Attendance Marked successfully!');
        redirect('timetable/view_timetable');
    }

    public function all_discount_details($student_id)
    {
        $this->db->select('*');
        $this->db->from('discounts_approval');
        $this->db->where("student_id = '$student_id'");
        $data['discounts'] = $this->db->get()->result_array();

        $data['student'] = $this->db->get_where("students","student_id = '$student_id'")->row();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/discount_details', $data);
        $this->load->view('inc/footer');

    }

    public function reverse_discount()
    {
        $update_request = $this->db->get_where('discounts_approval',array('id'=>$this->input->post('rev_id')))->result_array();

        $revamount    = $this->input->post('rev_amount');
        $installments = $this->input->post('installments');

        $payment_date = $this->db->order_by("id","desc")->limit(1)->get_where('payments', array('student_id'=>$update_request[0]['student_id']))->row()->dead_line;

        $this->db->set('total_fee', 'total_fee +' . $revamount . '', false);
        $this->db->where(array('student_id'=>$update_request[0]['student_id']));
        $this->db->update('students');

        $payment_plan = $this->db->get_where('students', array('student_id'=>$update_request[0]['student_id']))->row()->plan_id;
        $plan = $this->db->get_where('fee_rules', array('fee_rule_id'=>$payment_plan))->result_array();
        $plan=$plan[0];

        $payment_plan = $this->db->order_by("id","desc")->limit(1)->get_where('payments', array('student_id'=>$update_request[0]['student_id']))->row()->dead_line;
        $permonth = $revamount/$installments;
        for($i=1; $i<=$installments; $i++)
        {

            $dead_line = $date = date('Y-m-10', strtotime('first day of +' . $i . ' month', strtotime($payment_date)));
            $challan_no = $this->getChallanNo();

            $this->db->set('amount', $permonth);
            $this->db->set('dead_line', $dead_line);
            $this->db->set('student_id', $update_request[0]['student_id']);
            $this->db->set('payment_plan', "Auto");
            $this->db->set('disc_per_inst', $plan['disc_per_inst']);
            $this->db->set('challan_no', $challan_no);
            $this->db->set('payment_comment', 'College Fee');
            $this->db->set('add_by',$this->session->userdata('name'));
            $this->db->set('last_edit',$this->session->userdata('name'));
            $this->db->insert('payments');
        }

        $this->db->set('status',3);
        $this->db->where(array('id'=>$this->input->post('rev_id')));
        $this->db->update('discounts_approval');

        $this->session->set_flashdata('message', 'Discount Reversed successfully.');
        redirect('students/all_discount_details/'.$update_request[0]['student_id']);
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
//        $response = json_decode($result);
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
        }else
		{
			print_r($result);
			exit();
		}
//        $results=$this->db->get_where("students_payments","payment_id = '$insert_id'")->result_array();

    }

    public function admission_letter_print($id)
    {
        $qry = "SELECT DISTINCT `merged_challan`, discount as discount FROM `payments` WHERE student_id=$id and merged_challan is not null GROUP by merged_challan UNION ALL SELECT DISTINCT `merged_challan`, sum(discount) as discount FROM `payments` WHERE student_id=$id and merged_challan is null";
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

        //$data['payments'] = $this->db->order_by('dead_line','ASC')->get_where('payments',"student_id = '$id'")->result_array();
        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where(array('student_id'=>$id,'paid'=>0));
        $this->db->order_by('dead_line','ASC');
        $data['unpaid_payments'] = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('payments');
        $this->db->where(array('student_id'=>$id,'paid'=>1));
        $this->db->order_by('dead_line','ASC');
        $data['paid_payments'] = $this->db->get()->result_array();

        $data['student'] = $this->student->getSingleStudent($id);
        $data['discount'] = $this->student->getStudentDiscount($id);
        $data['rules'] = $this->db->get('admission_rules_regulations')->row();

        $this->load->view('students/admission_payment_letter', $data);
    }

    public function start_process($studentid)
    {
        $last_count = $this->db->order_by('struck_of_id','DESC')->limit(1)->get_where("struckof_procedures","student_id = $studentid")->row();
        if ( $last_count )
            $last_count = ($last_count->process_count) + 1;
        else
            $last_count = 1;

        $this->db->set('student_id', $studentid);
        $this->db->set('process_count', $last_count);
        $this->db->set('action_type', $this->input->post('delete_type'));
        $this->db->set('reason', $this->input->post('reason_detail'));
        $this->db->set('status', 'pending');
        $this->db->set('created_at', date('Y-m-d H:i:s'));
        $this->db->set('created_by', $this->session->userdata('name'));
        $this->db->insert('struckof_procedures');

        redirect('students/struckofstudentview/'.$studentid);
    }

    public function struckofstudentviewprocess($studentid,$struck_of_id)
    {
        $access = checkUserAccess();
        $class_ids = @explode(',',$access[0]['class_ids']);

        if($studentid!=''){
            $this->db->select('students.*,campuses.campus_name, courses.course_name ,classes.name as class_name,machine_data.machine_id');
            $this->db->from('students');            $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
            $this->db->join('machine_data', 'machine_data.teacher_student_id=students.student_id', 'inner');
            $this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'left');
            $this->db->join('courses', 'courses.course_id=students.course_id', 'left');
            $this->db->where("(students.student_id = '".$studentid."')", NULL, FALSE);

            if($this->session->userdata('role')!='Admin'){
                $this->db->where_in('classes.class_id', $class_ids);
            }
            $data['students'] = $this->db->get()->result_array();

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

            $this->db->select('*');
            $this->db->from('struckofdetails_students');
            $this->db->where("struckofdetails_students.student_id = '".$studentid."' and process_count = $struck_of_id");
            $data['struckofdata'] = $this->db->get()->result_array();

            $data['studentid'] = $studentid;
        }else{
            $data['students'] = null;
        }
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/struckofstudent_new', $data);
        $this->load->view('inc/footer');
    }

    public function payment_reversal_process($studentid)
    {
        $student_id = $this->input->post('student_id');
        $payment_id = $this->input->post('payment_id');
        $reversal_amount = $this->input->post('reversal_amount');
        $reversal_reason = $this->input->post('reversal_reason');
        $created_by = $this->session->userdata('name');
        //load the helper
        $this->load->helper('form');

        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'uploads/';

        // set the filter image types
        $config['allowed_types'] = '*';

        //load the upload library
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->set_allowed_types('*');
        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('reversal_application')) {
            $data = array('msg' => $this->upload->display_errors());
            $reversal_application = '';
        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $reversal_application = $data['upload_data']['file_name'];
            }
        }

        $this->db->set('student_id',$student_id);
        $this->db->set('payment_id',$payment_id);
        $this->db->set('reversal_amount',$reversal_amount);
        $this->db->set('reversal_reason',$reversal_reason);
        $this->db->set('created_by',$created_by);
        $this->db->set('reversal_application',$reversal_application);

        $this->db->insert('payments_reversal_requests');

        $this->session->set_flashdata('message', 'Payment Reversal Request has been submitted.');
        redirect(site_url().'/students/payments_paid/'.$studentid);

        /*$this->db->select('*');
        $this->db->from('fee_reversals');
        $this->db->where("student_id = '".$studentid."' and status='pending'");
        $checkRequest = $this->db->get()->result_array();

        if(count($checkRequest)>0)
        {
            $this->session->set_flashdata('error', 'Fee Reverse Request is already been submitted. Kindly clear your previous request first.');
            redirect(site_url().'/students/payments_paid/'.$studentid);
        }

        //load the helper
        if (!file_exists(getcwd().'/reversal_images')) {
            mkdir(getcwd().'/reversal_images', 0777);
        }
        $this->load->helper('form');
        $config['upload_path'] = 'reversal_images/';
        $config['allowed_types'] = 'gif|jpg|png';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
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

        $rev_amount = $this->input->post('rev_amount');
        $reason = $this->input->post('reason');
        $fee_paid = $this->input->post('reversible_amount');

        if ($this->input->post('type'))
            $this->db->set('paid_challans',$this->input->post('type'));
        $this->db->set('student_id',$studentid);
        $this->db->set('fee_paid',$fee_paid);
        $this->db->set('reason',$reason);
        $this->db->set('reverse_amount',$rev_amount);
        $this->db->set('file',$image);
        $this->db->set('created_by',$this->session->userdata('user_id'));
        $this->db->set('created_at',date('Y-m-d H:i:s'));
        $this->db->insert('fee_reversals');
        redirect(site_url().'/students/payments_paid/'.$studentid);*/
    }

    public function payment_reversal_process_complete($student_id)
    {
        $payment_id = $this->input->post('payment_id');
        $reversal_payment = $this->db->get_where('payments_reversal_requests',array('payment_id'=>$payment_id,'student_id'=>$student_id))->result_array();

        $mypettycash = my_pettycash();

        if($reversal_payment[0]['reversal_amount']>$mypettycash)
        {
            $this->session->set_flashdata('error', 'There is not enough money in your petty cash for the reversal of this payment. Kindly add amount in your petty cash.');
            redirect(site_url().'/students/payments_paid/'.$student_id);
        }
        else
        {
            //load the helper
            $this->load->helper('form');

            //Configure
            //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
            $config['upload_path'] = 'uploads/';

            // set the filter image types
            $config['allowed_types'] = '*';

            //load the upload library
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            $this->upload->set_allowed_types('*');
            $data['upload_data'] = '';

            //if not successful, set the error message
            if (!$this->upload->do_upload('proof')) {
                $data = array('msg' => $this->upload->display_errors());
                $proof_image = '';
            }
            else
            {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if($data['upload_data']['file_name']){
                    $proof_image = $data['upload_data']['file_name'];
                }
            }

            //UPDATE PAYMENT REVERSAL TABLE
            $this->db->set('proof_image',$proof_image);
            $this->db->set('done',1);
            $this->db->set('paid_by',$this->session->userdata('name'));
            $this->db->where('payment_id',$payment_id);
            $this->db->update('payments_reversal_requests');

            //ADD AMOUNT IN EXPENSE
            $this->db->select('payments.*,classes.campus_id');
            $this->db->from('payments');
            $this->db->join('students','payments.student_id=students.student_id','inner');
            $this->db->join('classes','classes.class_id=students.class_id','inner');
            $this->db->where('payments.id',$payment_id);
            $payment_details = $this->db->get()->result_array();

            $this->db->set('date',date('Y-m-d'));
            $this->db->set('actual_date', date('Y-m-d H:i:s'));
            $this->db->set('title', 'Payment Reversal Against Challan # '.$payment_details[0]['paid_challans']);
            $this->db->set('amount', $reversal_payment[0]['reversal_amount']);
            $this->db->set('add_by', $this->session->userdata('name'));
            $this->db->set('add_by_id', $this->session->userdata('user_id'));
            $this->db->set('last_edit', $this->session->userdata('name'));
            $this->db->set('payment_type', 'cash');
            $this->db->set('paid_type', 'cash');
            $this->db->set('approved_status', 1);
            $this->db->set('campus_id', $payment_details[0]['campus_id']);
            $this->db->set('expense_category_id', 26);
            $this->db->insert('expenses');

            //UPDATE PETTY CASH
            $this->db->set('remaining_amount', 'remaining_amount -'.$reversal_payment[0]['reversal_amount'].'',false);
            $this->db->where('assign_to', $this->session->userdata('user_id'));
            $this->db->update('petty_cash_college_wise');

            $this->session->set_flashdata('message', 'Amount Reversal Successfully.');
            redirect(site_url().'/students/payments_paid/'.$student_id);
        }
    }

    public function occupation()
    {
        $data['occupations'] = $this->db->get_where('occupations',array('sub_of'=>0))->result_array();
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/occupation',$data);
        $this->load->view('inc/footer');
    }

    public function add_occupation()
    {
        $occupation_name = $this->input->post('occupation_name');
        $this->db->set('occupation_name',$occupation_name);
        $this->db->set('has_sub',0);
        $this->db->set('sub_of',0);
        $this->db->insert('occupations');

        $this->session->set_flashdata('message','Occupation Added Successfully');
        redirect('students/occupation');
    }

    public function add_sub_occupation()
    {
        $sub_of = $this->input->post('head_category');
        $occupation_name = $this->input->post('occupation_name');

        $this->db->set('occupation_name',$occupation_name);
        $this->db->set('has_sub',0);
        $this->db->set('sub_of',$sub_of);
        $this->db->insert('occupations');

        $this->db->set('has_sub',1);
        $this->db->where('occupation_id',$sub_of);
        $this->db->update('occupations');

        $this->session->set_flashdata('message','Sub Occupation Added Successfully');
        redirect('students/occupation');
    }

    public function edit_occupation($occupation_id)
    {
        $data['occupation'] = $this->db->get_where('occupations',array('occupation_id'=>$occupation_id))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/edit_occupation',$data);
        $this->load->view('inc/footer');
    }

    public function update_occupation($occupation_id)
    {
        $occupation_name = $this->input->post('occupation_name');
        $this->db->set('occupation_name',$occupation_name);
        $this->db->where('occupation_id',$occupation_id);
        $this->db->update('occupations');

        $this->session->set_flashdata('message','Occupation Updated Successfully');
        redirect('students/edit_occupation/'.$occupation_id);
    }

    public function generate_documents()
    {
        $data['courses'] = $this->db->get('courses')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/generate_documents',$data);
        $this->load->view('inc/footer');
    }

    public function getCampuseByCourseId()
    {
        $course_id = $this->input->post('course_id');

        $course = $this->db->get_where('courses',array('course_id'=>$course_id))->result_array();
        $campus_ids = explode(',',$course[0]['campus_ids']);

        $this->db->select('*');
        $this->db->from('campuses');
        $this->db->where_in('campus_id',$campus_ids);
        $campuses = $this->db->get()->result_array();

        $html='';
        $html.='<option value="">SELECT CAMPUS</option>';
        foreach($campuses as $campus)
        {
            $html.='<option value="'.$campus['campus_id'].'">'.$campus['campus_name'].'</option>';
        }
        echo $html;
    }

    public function getClassesByCampusId()
    {
        $campus_id = $this->input->post('campus_id');

        $this->db->select('*');
        $this->db->from('classes');
        $this->db->where('campus_id',$campus_id);
        $this->db->where('status',1);
        $classes = $this->db->get()->result_array();

        $html='';
        $html.='<option value="">SELECT CLASS</option>';
        foreach($classes as $class)
        {
            $html.='<option value="'.$class['class_id'].'">'.$class['name'].'</option>';
        }
        echo $html;
    }

    public function getAllStudents()
    {
        $class_id = $this->input->post('myclass');

        //$qry = 'SELECT * FROM students WHERE class_id="'.$class_id.'" AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
        $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name, CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender FROM students WHERE class_id="'.$class_id.'" AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
        $students = $this->db->query($qry)->result_array();
        $html='';
        $html.='<table class="table table-striped table-bordered table-hover" id="sample_2">';
        $html.='<thead>';
        $html.='<tr>';
        $html.='<th class="hidden">hidden</th>';
        $html.='<th>Sr no.</th>';
        $html.='<th>Roll No.</th>';
        $html.='<th>Student Name</th>';
        $html.='<th>CNIC</th>';
        $html.='<th>Postal Address</th>';
        $html.='<th>Documents</th>';
        $html.='</tr>';
        $html.='</thead>';
        $html.='<tbody>';
        $i=1;

        foreach($students as $student):

            if($student['gender']=='Male')
            {
                $name = ucfirst(strtolower($student['first_name'])).' '.ucfirst(strtolower($student['last_name'])).'<br />S/O '.ucfirst(strtolower($student['father_name']));
            }
            else
            {
                $name = ucfirst(strtolower($student['first_name'])).' '.ucfirst(strtolower($student['last_name'])).'<br />D/O '.ucfirst(strtolower($student['father_name']));
            }
            $photo = array();
            $html.='<tr class="odd gradeX">';
            $html.='<td class="hidden">'.$i.'</td>';
            $html.='<td>'.$i.'</td>';
            $html.='<td>'.$student['roll_no'].'</td>';
            $html.='<td>'.$name.'</td>';
            $html.='<td>'.$student['cnic'].'</td>';
            $html.='<td>'.$student['address'].'</td>';
            //PHOTO CHECK
            $photo = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->result_array();
            if(count($photo)>0)
            {
                $photo_checker = '<button class="btn green"><i class="fa fa-check"></i> Photo</button>';
            }
            else
            {
                $photo_checker = '<button class="btn red"><i class="fa fa-remove"></i> Photo</button>';
            }
            //RESULT CARD CHECK
            $result_card = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Result Card'))->result_array();
            if(count($result_card)>0)
            {
                $result_card_checker = '<button class="btn green"><i class="fa fa-check"></i> Result Card</button>';
            }
            else
            {
                $result_card_checker = '<button class="btn red"><i class="fa fa-remove"></i> Result Card</button>';
            }
            //ID CARD CHECK
            $id_card = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'ID Card'))->result_array();
            if(count($id_card)>0)
            {
                $id_card_checker = '<button class="btn green"><i class="fa fa-check"></i> Identity</button>';
            }
            else
            {
                $id_card_checker = '<button class="btn red"><i class="fa fa-remove"></i> Identity</button>';
            }
            //BFORM CHECK
            if(count($id_card)<1)
            {
                $bform = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'B - FORM'))->result_array();
                if(count($bform)>0)
                {
                    $id_card_checker = '<button class="btn green"><i class="fa fa-check"></i> Identity</button>';
                }
                else
                {
                    $id_card_checker = '<button class="btn red"><i class="fa fa-remove"></i> Identity</button>';
                }
            }

            $html.='<td>'.$photo_checker.$id_card_checker.$result_card_checker.'</td>';
            $html.='</tr>';
            $i++;
        endforeach;
        $html.='</tbody>';
        $html.='</table>';

        $html.='<a class="btn green" target="_blank" href="'.site_url('students/download/'.$class_id).'">Download Excel File</a>';
        $html.='<a class="btn green" target="_blank" href="'.site_url('students/download_photos/'.$class_id).'">Download Photos</a>';
        $html.='<a class="btn green" target="_blank" href="'.site_url('students/download_cnic/'.$class_id).'">Download CNIC</a>';
        $html.='<a class="btn green" target="_blank" href="'.site_url('students/download_result_card/'.$class_id).'">Download Result Card</a>';

        echo $html;
    }

    public function filterData(&$str){ 
        $str = preg_replace("/\t/", "\\t", $str); 
        $str = preg_replace("/\r?\n/", "\\n", $str); 
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
    } 

    public function download($class_id='')
    {
        //$conn = mysqli_connect("localhost", "root", "test", "phppot_examples");
        if(!empty($class_id))
        {
            $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name,CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender FROM students WHERE class_id="'.$class_id.'" AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
            $students = $this->db->query($qry)->result_array();
        }
        else
        {
            $student_ids = $this->input->post('student_ids');
            $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name,CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender FROM students WHERE student_id IN ('.$student_ids.') AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
            //echo $qry;
            $students = $this->db->query($qry)->result_array();
        }

        $filename = "students_data.csv";
        $fp = fopen('php://output', 'w+');

        //$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='phppot_examples' AND TABLE_NAME='toy'";
        $headings = array('Sr no.','Name', 'CNIC', 'Postal Address');
        foreach($headings as $heading)
        {
            $header[] = $heading;
        }

        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$filename);
        fputcsv($fp, $header);
        $i=1;
        foreach($students as $student)
        {
            if($student['gender']=='Male')
            {
                $name = ucfirst(strtolower($student['first_name'])).' '.ucfirst(strtolower($student['last_name'])).PHP_EOL.'S/O '.ucfirst(strtolower($student['father_name']));
            }
            else
            {
                $name = ucfirst(strtolower($student['first_name'])).' '.ucfirst(strtolower($student['last_name'])).PHP_EOL.'D/O '.ucfirst(strtolower($student['father_name']));
            }
            $sr=array($i,$name,$student['cnic'],$student['address']);
            fputcsv($fp, $sr);
            $i++;
        }
        exit;
    }

    public function new_download($class_id='')
    {
        //$conn = mysqli_connect("localhost", "root", "test", "phppot_examples");
        if(!empty($class_id))
        {
            $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name,CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender,class_id FROM students WHERE class_id="'.$class_id.'" AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
            $students = $this->db->query($qry)->result_array();
        }
        else
        {
            $student_ids = $this->input->post('student_ids');
            $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name,CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender,class_id FROM students WHERE student_id IN ('.$student_ids.') AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
            //echo $qry;
            $students = $this->db->query($qry)->result_array();
        }

        $filename = "students_data.csv";
        $fp = fopen('php://output', 'w+');

        //$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='phppot_examples' AND TABLE_NAME='toy'";
        $headings = array('Sr. Number','Enrolment Number', 'Previous First Year Pass Roll Number (Exam Wise)', 'CNIC Number','Name','Father Name','Session');
        foreach($headings as $heading)
        {
            $header[] = $heading;
        }

        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$filename);
        fputcsv($fp, $header);
        $i=1;
        foreach($students as $student)
        {
            if($student['gender']=='Male')
            {
                $name = ucfirst(strtolower($student['first_name'])).' '.ucfirst(strtolower($student['last_name']));
            }
            else
            {
                $name = ucfirst(strtolower($student['first_name'])).' '.ucfirst(strtolower($student['last_name']));
            }
            
            $father_name = ucfirst(strtolower($student['father_name']));

            $session = $this->db->get_where('classes',array('class_id'=>$student['class_id']))->row()->session;

            $class = $this->input->post('class');
            $council_exam_no = $this->input->post('council_exam_no');

            $this->db->order_by('id','DESC');
            $this->db->limit(1);
            $enrolment_no = @$this->db->get_where('punjab_council_roll_number',array('class'=>1,'cnic'=>$student['cnic']))->row()->computer_no;

            if($enrolment_no=='')
            {
                $this->db->order_by('id','DESC');
                $this->db->limit(1);
                $enrolment_no = @$this->db->get_where('punjab_council_roll_number',array('class'=>1,'cnic'=>$student['cnic']))->row()->computer_no;
            }

            $last_pass_enrolment_no = @$this->db->get_where('punjab_council_roll_number',array('class'=>1,'cnic'=>$student['cnic'],'result_remarks'=>'Pass'))->row()->roll_no;

            if($last_pass_enrolment_no=='')
            {
                $last_pass_enrolment_no = @$this->db->get_where('punjab_council_roll_number',array('class'=>1,'cnic'=>$student['cnic'],'result_remarks'=>'Pass*'))->row()->roll_no;
            }
            
            $sr=array($i,$enrolment_no,$last_pass_enrolment_no,$student['cnic'],$name,$father_name,$session);
            fputcsv($fp, $sr);
            $i++;
        }
        exit;
    }

    public function download_photos($class_id='')
    {
        echo '<h1 style="text-align:center;">Kindly Wait 5-10 Minutes While We are creating Files...</h1>';
        
        if(!empty($class_id))
        {
            $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name,CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender FROM students WHERE class_id="'.$class_id.'" AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
            $students = $this->db->query($qry)->result_array();

            if (!is_dir('students_data')) {
                mkdir('./students_data', 0777, TRUE);
            }
    
            if (!is_dir('students_data/photos/'.$class_id)) {
                mkdir('./students_data/photos/'.$class_id, 0777, TRUE);
            }
        }
        else
        {
            $student_ids = $this->input->post('student_ids');
            $class_id = $this->input->post('class_id');
            $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name,CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender FROM students WHERE student_id IN ('.$student_ids.') AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
            $students = $this->db->query($qry)->result_array();

            if (!is_dir('students_data')) {
                mkdir('./students_data', 0777, TRUE);
            }
    
            if (!is_dir('students_data/photos/'.$class_id)) {
                mkdir('./students_data/photos/'.$class_id, 0777, TRUE);
            }
        }

        $i=1;
        foreach($students as $student)
		{
			$documents = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Photo'))->result_array();
			$this->db->order_by('id','DESC');
			$this->db->limit(1);
			$computer_no = $this->db->get_where('punjab_council_roll_number',array('cnic'=>$student['cnic']))->result_array();
			$computer_no = @$computer_no[0]['computer_no'];
			
			if($computer_no=='')
			{
			    $computer_no = $student['student_id'];
			}
			
			foreach($documents as $document)
			{
				//SAVE STUDENT PHOTO
                if($document['online_image']!='')
                {
                    $image_link = $document['online_image'];
                    $array = explode('.', $image_link);
                    $extension = end($array);
                    $new_link = './students_data/photos/'.$class_id.'/'.$computer_no.'.'.$extension;
                    copy($image_link, $new_link);
                }
                elseif($document['online_image']=='' && $document['image']!='')
                {
                    $image_link = base_url().'uploads/'.$document['image'];
                    $array = explode('.', $image_link);
                    $extension = end($array);
                    $new_link = './students_data/photos/'.$class_id.'/'.$computer_no.'.'.$extension;
                    copy($image_link, $new_link);
                }
                else
                {

                }
			}
            $i++;
		}

		// Get real path for our folder
		$rootPath = realpath('./students_data/photos/'.$class_id);

		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open('photos.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
	
		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
	
		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);
	
				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}
	
		// Zip archive will be created only after closing object
		$zip->close();

		// folder path that contains files and subfolders
		$path = './students_data/photos/'.$class_id;
		
		// call the function
		$this->deleteAll($path);

        redirect(base_url('photos.zip'));
		//echo '<a href="'.base_url('file.zip').'" class="btn green"><i class="fa fa-download"></i> Download File</a>';
		exit;
    }

    public function download_cnic($class_id='')
    {
        echo '<h1 style="text-align:center;">Kindly Wait 5-10 Minutes While We are creating Files...</h1>';

        if(!empty($class_id))
        {
            $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name,CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender FROM students WHERE class_id="'.$class_id.'" AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
            $students = $this->db->query($qry)->result_array();

            if (!is_dir('students_data')) {
                mkdir('./students_data', 0777, TRUE);
            }
    
            if (!is_dir('students_data/cnic/'.$class_id)) {
                mkdir('./students_data/cnic/'.$class_id, 0777, TRUE);
            }
        }
        else
        {
            $student_ids = $this->input->post('student_ids');
            $class_id = $this->input->post('class_id');
            $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name,CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender FROM students WHERE student_id IN ('.$student_ids.') AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
            $students = $this->db->query($qry)->result_array();

            if (!is_dir('students_data')) {
                mkdir('./students_data', 0777, TRUE);
            }
    
            if (!is_dir('students_data/cnic/'.$class_id)) {
                mkdir('./students_data/cnic/'.$class_id, 0777, TRUE);
            }
        }

        $i=1;
        foreach($students as $student)
		{
            $this->db->limit(1);
			$documents = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'ID Card'))->result_array();
			$this->db->order_by('id','DESC');
			$this->db->limit(1);
			$computer_no = $this->db->get_where('punjab_council_roll_number',array('cnic'=>$student['cnic']))->result_array();
			$computer_no = @$computer_no[0]['computer_no'];
			
			if($computer_no=='')
			{
			    $computer_no = $student['student_id'];
			}
			
			foreach($documents as $document)
			{
				//SAVE STUDENT CNIC
                if($document['online_image']!='')
                {
                    $image_link = $document['online_image'];
                    $array = explode('.', $image_link);
                    $extension = end($array);
                    $new_link = './students_data/cnic/'.$class_id.'/'.$computer_no.'.'.$extension;
                    copy($image_link, $new_link);
                }
                elseif($document['online_image']=='' && $document['image']!='')
                {
                    $image_link = base_url().'uploads/'.$document['image'];
                    $array = explode('.', $image_link);
                    $extension = end($array);
                    $new_link = './students_data/cnic/'.$class_id.'/'.$computer_no.'.'.$extension;
                    copy($image_link, $new_link);
                }
                else
                {

                }
			}
            if(count($documents)<1)
            {
                $this->db->limit(1);
			    $documents = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'B - FORM'))->result_array();
			    $this->db->order_by('id','DESC');
    			$this->db->limit(1);
    			$computer_no = $this->db->get_where('punjab_council_roll_number',array('cnic'=>$student['cnic']))->result_array();
    			$computer_no = @$computer_no[0]['computer_no'];
                foreach($documents as $document)
                {
                    //SAVE STUDENT CNIC
                    if($document['online_image']!='')
                    {
                        $image_link = $document['online_image'];
                        $array = explode('.', $image_link);
                        $extension = end($array);
                        $new_link = './students_data/cnic/'.$class_id.'/'.$computer_no.'.'.$extension;
                        copy($image_link, $new_link);
                    }
                    elseif($document['online_image']=='' && $document['image']!='')
                    {
                        $image_link = base_url().'uploads/'.$document['image'];
                        $array = explode('.', $image_link);
                        $extension = end($array);
                        $new_link = './students_data/cnic/'.$class_id.'/'.$computer_no.'.'.$extension;
                        copy($image_link, $new_link);
                    }
                    else
                    {

                    }
                }
            }
            $i++;
		}

		// Get real path for our folder
		$rootPath = realpath('./students_data/cnic/'.$class_id);

		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open('cnic.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
	
		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
	
		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);
	
				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}
	
		// Zip archive will be created only after closing object
		$zip->close();

		// folder path that contains files and subfolders
		$path = './students_data/cnic/'.$class_id;
		
		// call the function
		$this->deleteAll($path);

        redirect(base_url('cnic.zip'));
		//echo '<a href="'.base_url('file.zip').'" class="btn green"><i class="fa fa-download"></i> Download File</a>';
		exit;
    }

    public function download_result_card($class_id='')
    {
        echo '<h1 style="text-align:center;">Kindly Wait 5-10 Minutes While We are creating Files...</h1>';
        
        if(!empty($class_id))
        {
            $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name,CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender FROM students WHERE class_id="'.$class_id.'" AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
            $students = $this->db->query($qry)->result_array();

            if (!is_dir('students_data')) {
                mkdir('./students_data', 0777, TRUE);
            }
    
            if (!is_dir('students_data/result_cards/'.$class_id)) {
                mkdir('./students_data/result_cards/'.$class_id, 0777, TRUE);
            }
        }
        else
        {
            $student_ids = $this->input->post('student_ids');
            $class_id = $this->input->post('class_id');
            $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name,CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender FROM students WHERE student_id IN ('.$student_ids.') AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
            $students = $this->db->query($qry)->result_array();

            if (!is_dir('students_data')) {
                mkdir('./students_data', 0777, TRUE);
            }
    
            if (!is_dir('students_data/result_cards/'.$class_id)) {
                mkdir('./students_data/result_cards/'.$class_id, 0777, TRUE);
            }
        }

        $i=1;
        foreach($students as $student)
		{
		    $lastCouncilexam = $this->db->order_by("id","DESC")->get_where('punjab_council_roll_number',array('cnic'=>@$student['cnic']))->row();
		    $image=$this->db->get_where('punjab_council_roll_number','cnic = "'.$student['cnic'].'" and class = "1" and council_exam_no = "'.$lastCouncilexam->council_exam_no.'"')->result_array();
		    
		    $this->db->order_by('id','DESC');
			$this->db->limit(1);
			$computer_no = $this->db->get_where('punjab_council_roll_number',array('cnic'=>$student['cnic']))->result_array();
			$computer_no = @$computer_no[0]['computer_no'];
		    
			//SAVE STUDENT CNIC
            if($image[0]['online_result_image']!='')
            {
                $image_link = $image[0]['online_result_image'];
                $array = explode('.', $image_link);
                $extension = end($array);
                $new_link = './students_data/result_cards/'.$class_id.'/'.$computer_no.'.'.$extension;
                copy($image_link, $new_link);
            }
            elseif($image[0]['online_result_image']=='' && $image[0]['result_image']!='')
            {
                $image_link = base_url().$image[0]['result_image'];
                $array = explode('.', $image_link);
                $extension = end($array);
                $new_link = './students_data/result_cards/'.$class_id.'/'.$computer_no.'.'.$extension;
                copy($image_link, $new_link);
            }
            else
            {

            }
			
            $i++;
		}

		// Get real path for our folder
		$rootPath = realpath('./students_data/result_cards/'.$class_id);

		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open('result_cards.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
	
		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
	
		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);
	
				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}
	
		// Zip archive will be created only after closing object
		$zip->close();

		// folder path that contains files and subfolders
		$path = './students_data/result_cards/'.$class_id;
		
		// call the function
		$this->deleteAll($path);

        redirect(base_url('result_cards.zip'));
		//echo '<a href="'.base_url('file.zip').'" class="btn green"><i class="fa fa-download"></i> Download File</a>';
		exit;
    }

    public function download_matric_result_card($class_id='')
    {
        echo '<h1 style="text-align:center;">Kindly Wait 5-10 Minutes While We are creating Files...</h1>';

        if(!empty($class_id))
        {
            $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name,CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender FROM students WHERE class_id="'.$class_id.'" AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
            $students = $this->db->query($qry)->result_array();

            if (!is_dir('students_data')) {
                mkdir('./students_data', 0777, TRUE);
            }
    
            if (!is_dir('students_data/matric_result_card/'.$class_id)) {
                mkdir('./students_data/matric_result_card/'.$class_id, 0777, TRUE);
            }
        }
        else
        {
            $student_ids = $this->input->post('student_ids');
            $class_id = $this->input->post('class_id');
            $qry = 'SELECT student_id ,roll_no, cnic, gender, first_name, last_name, father_name,CONCAT(first_name," ", last_name, " S/O ", father_name) as name, address, mobile,emergency_no, board, 03158042977 as institute,gender FROM students WHERE student_id IN ('.$student_ids.') AND status=1 ORDER BY CAST(roll_no as SIGNED INTEGER) ASC';
            $students = $this->db->query($qry)->result_array();

            if (!is_dir('students_data')) {
                mkdir('./students_data', 0777, TRUE);
            }
    
            if (!is_dir('students_data/matric_result_card/'.$class_id)) {
                mkdir('./students_data/matric_result_card/'.$class_id, 0777, TRUE);
            }
        }

        $i=1;
        foreach($students as $student)
		{
            $this->db->limit(1);
			$documents = $this->db->get_where('student_documents',array('student_id'=>$student['student_id'],'type'=>'Result Card'))->result_array();
			
			$this->db->order_by('id','DESC');
			$this->db->limit(1);
			$computer_no = $this->db->get_where('punjab_council_roll_number',array('cnic'=>$student['cnic']))->result_array();
			$computer_no = @$computer_no[0]['computer_no'];
			
			foreach($documents as $document)
			{
				//SAVE STUDENT MATRIC RESULT CARD
                if($document['online_image']!='')
                {
                    $image_link = $document['online_image'];
                    $array = explode('.', $image_link);
                    $extension = end($array);
                    $new_link = './students_data/matric_result_card/'.$class_id.'/'.$computer_no.'.'.$extension;
                    copy($image_link, $new_link);
                }
                elseif($document['online_image']=='' && $document['image']!='')
                {
                    $image_link = base_url().'uploads/'.$document['image'];
                    $array = explode('.', $image_link);
                    $extension = end($array);
                    $new_link = './students_data/matric_result_card/'.$class_id.'/'.$computer_no.'.'.$extension;
                    copy($image_link, $new_link);
                }
                else
                {
                    
                }
			}
            $i++;
		}

		// Get real path for our folder
		$rootPath = realpath('./students_data/matric_result_card/'.$class_id);

		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open('matric_result_card.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
	
		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
	
		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);
	
				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}
	
		// Zip archive will be created only after closing object
		$zip->close();

		// folder path that contains files and subfolders
		$path = './students_data/matric_result_card/'.$class_id;
		
		// call the function
		$this->deleteAll($path);

        redirect(base_url('matric_result_card.zip'));
		//echo '<a href="'.base_url('file.zip').'" class="btn green"><i class="fa fa-download"></i> Download File</a>';
		exit;
    }

    public function deleteAll($dir, $remove = false) 
	{
		$structure = glob(rtrim($dir, "/").'/*');
		if (is_array($structure)) {
		foreach($structure as $file) {
		if (is_dir($file))
		$this->deleteAll($file,true);
		else if(is_file($file))
		unlink($file);
		}
		}
		if($remove)
		rmdir($dir);
	}

    public function getSubOccupation()
    {
        $occupation_id = $this->input->post('occupation_id');
        $count = $this->input->post('count');
        $count += 1;
        $occupations = $this->db->get_where('occupations', array('sub_of'=>$occupation_id))->result_array();
        $html='<div class="form-group" id="div-'.$count.'"><label class="col-md-3 control-label"></label> <div class="col-md-9">
                    <select class="form-control student_education" name="education[]" data-count="'.$count.'" id="occupation_id'.$count.'" required>';
        foreach($occupations as $occupation) {
            $html.='<option value="'.$occupation['occupation_id'].'">'.$occupation['occupation_name'].'</option>';
        }
        $html.="</select></div></div>";
        if (count($occupations) > 0)
            echo $html;
    }

    public function purchased_products($student_id)
    {
        $this->db->select('*');
        $this->db->from('products');
        $this->db->join('product_names','product_names.product_name_id=products.product_name_id','left');
        $this->db->join('users','users.user_id=products.sold_by','left');
        $this->db->join('campuses','campuses.campus_id=products.campus_id','left');
		$this->db->join('rooms','rooms.room_id=products.room_id','left');
		$this->db->join('subrooms','subrooms.subroom_id=products.subroom_id','left');
        $this->db->where(array('products.student_id'=>$student_id,'products.sold'=>1));
        $data['sold_products'] = $this->db->get_where()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/purchased_products',$data);
        $this->load->view('inc/footer');
    }
    
    public function check_attendance($class_id)
    {
        $checker = $this->db->get_where('classes',array('class_id'=>$class_id))->result_array();
        
        $course_id = $checker[0]['course_id'];
        $campus_id = $checker[0]['campus_id'];
        $session = $checker[0]['session'];
        
        $data['study_types'] = $this->db->get_where('study_type',array('course_id'=>$course_id))->result_array();
        
        $this->db->where("find_in_set($campus_id, campus_id)");
        $data['shifts'] = $this->db->get('shifts')->result_array();
        
        //FORM SUBMISSION
        
        if($this->input->post('submit')==1)
        {
            $data['from_date'] = $this->input->post('from_date');
            $data['to_date'] = $this->input->post('to_date');
            $data['selected_study_type'] = $study_type = $this->input->post('study_type');
            $data['selected_shift'] = $shift = $this->input->post('shift');
            $data['section'] = $section = $this->input->post('section');
            
            $data['students'] = $this->db->get_where('students',array('class_id'=>$class_id,'section'=>$section,'shift'=>$shift,'study_type'=>$study_type))->result_array();
            
            $this->db->select('*');
            $this->db->from('lectures');
            $this->db->where('course',$course_id);
            $this->db->where('campus',$campus_id);
            $this->db->where('shift',$shift);
            $this->db->where('studytype',$study_type);
            if($section=='First Year')
            {
                $this->db->where('class',1);
            }
            else
            {
                $this->db->where('class',2);
            }
            
            $this->db->like('session',$session);
            
            $data['lectures'] = $this->db->get()->result_array();
        }
        else
        {
            $data['from_date'] = date('Y-m-01');
            $data['to_date'] = date('Y-m-d');
            $data['selected_study_type'] = '';
            $data['selected_shift'] = '';
            $data['section'] = '';
            
            $data['students'] = array();
            $data['lectures'] = array();
        }
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('students/check_attendance',$data);
        $this->load->view('inc/footer');
    }
    
    public function create_auto_fees($student_id)
    {
        //GET STUDENTS
        $students = $this->db->get_where('students',array('student_id'=>$student_id,'contractor_id'=>0))->result_array();
        
        echo 'Student ID : '.$student_id.'<br />';
        
        //GET STUDENT FEE PLAN
        $student_fee_plan = $this->db->get_where('fee_rules',array('fee_rule_id'=>@$students[0]['plan_id']))->result_array();
        
        if(count($student_fee_plan)==0)
        {
            $session = $this->db->get_where('classes',array('class_id'=>$students[0]['class_id']))->row()->session;
            
            $this->db->order_by('per_installment_fee','ASC');
            $this->db->limit(1);
            $student_fee_plan = $this->db->get_where('fee_rules',array('session'=>$session,'course_id'=>$students[0]['course_id']))->result_array();
        }
        
        if(count($student_fee_plan)>0)
        {
            $student_total_fee = $student_fee_plan[0]['total_fee'];
            
            echo 'Student Total Fee : '.$student_total_fee.'<br />';
            
            $total_discount = 0;
            
            // GET STUDENT DISCOUNT ON ADMISSION
            $admission_discount = $student_total_fee - $students[0]['total_fee'];
            
            $total_discount +=$admission_discount;
            
            echo 'Student Discount : '.$total_discount.'<br />';
            
            
            //GET STUDENTS CREATED FEES
            $this->db->select_sum('amount');
            $this->db->from('payments');
            $this->db->where('student_id',$student_id);
            $this->db->where('payment_comment','College Fee');
            $created_fees = $this->db->get()->result_array();
            
            echo 'Student Created Fee : '.$created_fees[0]['amount'].'<br />';
            
            $fee_not_created = ($student_total_fee-$total_discount)-$created_fees[0]['amount'];
            
            echo 'Fee Not Created : '.$fee_not_created.'<br />';
            
            $decided_per_installment_fee = $student_fee_plan[0]['per_installment_fee'];
            
            echo 'Decided Per installment fee : '.$decided_per_installment_fee.'<br />';
            
            
            
            $this->db->select('*');
            $this->db->from('payments');
            $this->db->where(array('student_id'=>$student_id,'payment_comment'=>'College Fee'));
            $this->db->order_by('dead_line','DESC');
            $this->db->limit(1);
            $last_payment = $this->db->get()->result_array();
            
            if(count($last_payment)>0)
            {
                $last_payment_dead_line = $last_payment[0]['dead_line'];
            }
            else
            {
                $last_payment_dead_line = date('Y-m-d');
            }
            
            echo 'Last Created Installment Date : '.$last_payment_dead_line.'<br />';
            
            $loop_count = round($fee_not_created/$decided_per_installment_fee);
            
            echo 'Loop : '.$loop_count.'<br />';
            
            //GET MAXIMUM LAST DATE OF FEE PLAN
            $maximum_fee_last_date = $student_fee_plan[0]['last_date'];
            
            echo 'Maximum Fee Last Date : '.$maximum_fee_last_date;
            
            
            $remainings = 0;
            
            for($i=1;$i<=$loop_count;$i++)
            {
                if($i==$loop_count)
                {
                    $time = strtotime($last_payment_dead_line);
                    $dead_line = date("Y-m-d", strtotime('+'.$i.' month', $time));
                    if($dead_line>$maximum_fee_last_date)
                    {
                        $case =1;
                    }
                    else
                    {
                        $case =2;
                    }
                }
            }
            
            echo 'Case : '.$case;
            
            
            if($case==1)
            {
                $start_date = new DateTime(date('Y-m-01'));
                $end_date = new DateTime($maximum_fee_last_date);
                
                // Set the desired day of the month
                $day_of_month = 10;
                
                // Array to store the dates
                $dates = [];
                
                // Loop through months between start and end dates
                while ($start_date <= $end_date) {
                    // Set the current date to the 10th of the current month
                    $current_date = new DateTime($start_date->format('Y-m') . '-' . str_pad($day_of_month, 2, '0', STR_PAD_LEFT));
                
                    // Check if the date is within the range
                    if ($current_date >= new DateTime(date('Y-m-01')) && $current_date <= $end_date) {
                        $dates[] = $current_date->format('Y-m-d');
                    }
                
                    // Move to the first day of the next month
                    $start_date->modify('first day of next month');
                }
                
                $total_months = count($dates);
                echo 'Total Months: '.$total_months;
                if($total_months>0)
                {
                    $installment_amount = $decided_per_installment_fee;
                
                    // Print the dates
                    foreach ($dates as $date) {
                        $fee_dead_line =  $date . PHP_EOL;
                        
                        //CREATE FEE
                        $challan_no = $this->getChallanNo();
                        
                        if ($date === end($dates))
                        {
                            $amount = $fee_not_created;
                        }
                        else
                        {
                            $amount = floor($installment_amount / 100) * 100;
                            $fee_not_created = $fee_not_created-$amount;
                        }
            
                        if($amount>0)
                        {
                            $this->db->set('amount', $amount);
                            $this->db->set('dead_line', $fee_dead_line);
                            $this->db->set('student_id', $student_id);
                            $this->db->set('payment_plan', 'Custom Plan');
                            $this->db->set('payment_comment', 'College Fee');
                            $this->db->set('challan_no', @$challan_no);
                            $this->db->set('special_comment', 'This Fee is created by auto fee generator system on '.date('Y-m-d'));
                            $this->db->set('add_by', 'System');
                            $this->db->set('last_edit', 'System');
                            $this->db->insert('payments');   
                        }
                    }   
                }
                else
                {
                    //CREATE FEE
                    $challan_no = $this->getChallanNo();
        
                    $this->db->set('amount', $fee_not_created);
                    $this->db->set('dead_line', $maximum_fee_last_date);
                    $this->db->set('student_id', $student_id);
                    $this->db->set('payment_plan', 'Custom Plan');
                    $this->db->set('payment_comment', 'College Fee');
                    $this->db->set('challan_no', @$challan_no);
                    $this->db->set('special_comment', 'This Fee is created by auto fee generator system on '.date('Y-m-d'));
                    $this->db->set('add_by', 'System');
                    $this->db->set('last_edit', 'System');
                    $this->db->insert('payments');
                }
            }
            elseif($case==2)
            {
                for($i=1;$i<=$loop_count;$i++)
                {
                    if($i==$loop_count)
                    {
                        $time = strtotime($last_payment_dead_line);
                        $dead_line = date("Y-m-d", strtotime('+'.$i.' month', $time));
                        echo 'Last Installment : '.$fee_not_created.' Paid on '.$dead_line.'<br />';
                        
                        //CREATE FEE
                        $challan_no = $this->getChallanNo();
            
                        $this->db->set('amount', $fee_not_created);
                        $this->db->set('dead_line', $dead_line);
                        $this->db->set('student_id', $student_id);
                        $this->db->set('payment_plan', 'Custom Plan');
                        $this->db->set('payment_comment', 'College Fee');
                        $this->db->set('challan_no', @$challan_no);
                        $this->db->set('special_comment', 'This Fee is created by auto fee generator system on '.date('Y-m-d'));
                        $this->db->set('add_by', 'System');
                        $this->db->set('last_edit', 'System');
                        $this->db->insert('payments');
                    }
                    else
                    {
                        $time = strtotime($last_payment_dead_line);
                        $dead_line = date("Y-m-d", strtotime('+'.$i.' month', $time));
                        echo 'Installment Amount : '.$decided_per_installment_fee.' Paid on '.$dead_line.'<br />';
                        $fee_not_created = $fee_not_created-$decided_per_installment_fee;
                        
                        //CREATE FEE
                        $challan_no = $this->getChallanNo();
            
                        $this->db->set('amount', $decided_per_installment_fee);
                        $this->db->set('dead_line', $dead_line);
                        $this->db->set('student_id', $student_id);
                        $this->db->set('payment_plan', 'Custom Plan');
                        $this->db->set('payment_comment', 'College Fee');
                        $this->db->set('challan_no', @$challan_no);
                        $this->db->set('special_comment', 'This Fee is created by auto fee generator system on '.date('Y-m-d'));
                        $this->db->set('add_by', 'System');
                        $this->db->set('last_edit', 'System');
                        $this->db->insert('payments');
                    }
                }
            }
        }
        $this->session->set_flashdata('message', 'Auto Payment Generate Successfully.');
        redirect(site_url().'/students/payments_paid/'.$student_id);
    }
    
    public function auto_fee_generator_cron()
    {
        $this->db->limit(5);
        $students = $this->db->get_where('students',array('chk'=>0,'status'=>1))->result_array();
        
        foreach($students as $student)
        {
            $this->db->set('chk',1);
            $this->db->where('student_id',$student['student_id']);
            $this->db->update('students');
            
            $this->create_auto_fee($student['student_id']);
        }
        
        if(count($students)<1)
        {
            $this->db->set('chk',0);
            $this->db->where('chk',1);
            $this->db->update('students');
        }
    }
    
    public function create_auto_fee($student_id)
    {
        //GET STUDENTS
        $students = $this->db->get_where('students',array('student_id'=>$student_id,'contractor_id'=>0))->result_array();
        
        echo 'Student ID : '.$student_id.'<br />';
        
        //GET STUDENT FEE PLAN
        $student_fee_plan = $this->db->get_where('fee_rules',array('fee_rule_id'=>@$students[0]['plan_id']))->result_array();
        
        if(count($student_fee_plan)==0)
        {
            $session = $this->db->get_where('classes',array('class_id'=>$students[0]['class_id']))->row()->session;
            
            $this->db->order_by('per_installment_fee','ASC');
            $this->db->limit(1);
            $student_fee_plan = $this->db->get_where('fee_rules',array('session'=>$session,'course_id'=>$students[0]['course_id']))->result_array();
        }
        
        if(count($student_fee_plan)>0)
        {
            $student_total_fee = $student_fee_plan[0]['total_fee'];
            
            echo 'Student Total Fee : '.$student_total_fee.'<br />';
            
            $total_discount = 0;
            
            // GET STUDENT DISCOUNT ON ADMISSION
            $admission_discount = $student_total_fee - $students[0]['total_fee'];
            
            $total_discount +=$admission_discount;
            
            echo 'Student Discount : '.$total_discount.'<br />';
            
            
            //GET STUDENTS CREATED FEES
            $this->db->select_sum('amount');
            $this->db->from('payments');
            $this->db->where('student_id',$student_id);
            $this->db->where('payment_comment','College Fee');
            $created_fees = $this->db->get()->result_array();
            
            echo 'Student Created Fee : '.$created_fees[0]['amount'].'<br />';
            
            $fee_not_created = ($student_total_fee-$total_discount)-$created_fees[0]['amount'];
            
            echo 'Fee Not Created : '.$fee_not_created.'<br />';
            
            $decided_per_installment_fee = $student_fee_plan[0]['per_installment_fee'];
            
            echo 'Decided Per installment fee : '.$decided_per_installment_fee.'<br />';
            
            
            
            $this->db->select('*');
            $this->db->from('payments');
            $this->db->where(array('student_id'=>$student_id,'payment_comment'=>'College Fee'));
            $this->db->order_by('dead_line','DESC');
            $this->db->limit(1);
            $last_payment = $this->db->get()->result_array();
            
            if(count($last_payment)>0)
            {
                $last_payment_dead_line = $last_payment[0]['dead_line'];
            }
            else
            {
                $last_payment_dead_line = date('Y-m-d');
            }
            
            echo 'Last Created Installment Date : '.$last_payment_dead_line.'<br />';
            
            $loop_count = round($fee_not_created/$decided_per_installment_fee);
            
            echo 'Loop : '.$loop_count.'<br />';
            
            //GET MAXIMUM LAST DATE OF FEE PLAN
            $maximum_fee_last_date = $student_fee_plan[0]['last_date'];
            
            echo 'Maximum Fee Last Date : '.$maximum_fee_last_date;
            
            
            $remainings = 0;
            
            for($i=1;$i<=$loop_count;$i++)
            {
                if($i==$loop_count)
                {
                    $time = strtotime($last_payment_dead_line);
                    $dead_line = date("Y-m-d", strtotime('+'.$i.' month', $time));
                    if($dead_line>$maximum_fee_last_date)
                    {
                        $case =1;
                    }
                    else
                    {
                        $case =2;
                    }
                }
            }
            
            echo 'Case : '.$case;
            
            
            if($case==1)
            {
                $start_date = new DateTime(date('Y-m-01'));
                $end_date = new DateTime($maximum_fee_last_date);
                
                // Set the desired day of the month
                $day_of_month = 10;
                
                // Array to store the dates
                $dates = [];
                
                // Loop through months between start and end dates
                while ($start_date <= $end_date) {
                    // Set the current date to the 10th of the current month
                    $current_date = new DateTime($start_date->format('Y-m') . '-' . str_pad($day_of_month, 2, '0', STR_PAD_LEFT));
                
                    // Check if the date is within the range
                    if ($current_date >= new DateTime(date('Y-m-01')) && $current_date <= $end_date) {
                        $dates[] = $current_date->format('Y-m-d');
                    }
                
                    // Move to the first day of the next month
                    $start_date->modify('first day of next month');
                }
                
                $total_months = count($dates);
                echo 'Total Months: '.$total_months;
                if($total_months>0)
                {
                    $installment_amount = $decided_per_installment_fee;
                
                    // Print the dates
                    foreach ($dates as $date) {
                        $fee_dead_line =  $date . PHP_EOL;
                        
                        //CREATE FEE
                        $challan_no = $this->getChallanNo();
                        
                        if ($date === end($dates))
                        {
                            $amount = $fee_not_created;
                        }
                        else
                        {
                            $amount = floor($installment_amount / 100) * 100;
                            $fee_not_created = $fee_not_created-$amount;
                        }
            
                        if($amount>0)
                        {
                            $this->db->set('amount', $amount);
                            $this->db->set('dead_line', $fee_dead_line);
                            $this->db->set('student_id', $student_id);
                            $this->db->set('payment_plan', 'Custom Plan');
                            $this->db->set('payment_comment', 'College Fee');
                            $this->db->set('challan_no', @$challan_no);
                            $this->db->set('special_comment', 'This Fee is created by auto fee generator system on '.date('Y-m-d'));
                            $this->db->set('add_by', 'System');
                            $this->db->set('last_edit', 'System');
                            $this->db->insert('payments');   
                        }
                    }   
                }
                else
                {
                    //CREATE FEE
                    $challan_no = $this->getChallanNo();
        
                    $this->db->set('amount', $fee_not_created);
                    $this->db->set('dead_line', $maximum_fee_last_date);
                    $this->db->set('student_id', $student_id);
                    $this->db->set('payment_plan', 'Custom Plan');
                    $this->db->set('payment_comment', 'College Fee');
                    $this->db->set('challan_no', @$challan_no);
                    $this->db->set('special_comment', 'This Fee is created by auto fee generator system on '.date('Y-m-d'));
                    $this->db->set('add_by', 'System');
                    $this->db->set('last_edit', 'System');
                    $this->db->insert('payments');
                }
            }
            elseif($case==2)
            {
                for($i=1;$i<=$loop_count;$i++)
                {
                    if($i==$loop_count)
                    {
                        $time = strtotime($last_payment_dead_line);
                        $dead_line = date("Y-m-d", strtotime('+'.$i.' month', $time));
                        echo 'Last Installment : '.$fee_not_created.' Paid on '.$dead_line.'<br />';
                        
                        //CREATE FEE
                        $challan_no = $this->getChallanNo();
            
                        $this->db->set('amount', $fee_not_created);
                        $this->db->set('dead_line', $dead_line);
                        $this->db->set('student_id', $student_id);
                        $this->db->set('payment_plan', 'Custom Plan');
                        $this->db->set('payment_comment', 'College Fee');
                        $this->db->set('challan_no', @$challan_no);
                        $this->db->set('special_comment', 'This Fee is created by auto fee generator system on '.date('Y-m-d'));
                        $this->db->set('add_by', 'System');
                        $this->db->set('last_edit', 'System');
                        $this->db->insert('payments');
                    }
                    else
                    {
                        $time = strtotime($last_payment_dead_line);
                        $dead_line = date("Y-m-d", strtotime('+'.$i.' month', $time));
                        echo 'Installment Amount : '.$decided_per_installment_fee.' Paid on '.$dead_line.'<br />';
                        $fee_not_created = $fee_not_created-$decided_per_installment_fee;
                        
                        //CREATE FEE
                        $challan_no = $this->getChallanNo();
            
                        $this->db->set('amount', $decided_per_installment_fee);
                        $this->db->set('dead_line', $dead_line);
                        $this->db->set('student_id', $student_id);
                        $this->db->set('payment_plan', 'Custom Plan');
                        $this->db->set('payment_comment', 'College Fee');
                        $this->db->set('challan_no', @$challan_no);
                        $this->db->set('special_comment', 'This Fee is created by auto fee generator system on '.date('Y-m-d'));
                        $this->db->set('add_by', 'System');
                        $this->db->set('last_edit', 'System');
                        $this->db->insert('payments');
                    }
                }
            }
        }
    }
    
    public function remove_admission_discount($student_id)
    {
        //GET STUDENT DETAILS
        $this_student = $this->db->get_where('students', array('student_id'=>$this->uri->segment(3)))->result_array();
        
        $total_fee = $this_student[0]['total_fee']+$this->input->post('rev_amount');
        
        $this->db->set('total_fee',$total_fee);
        $this->db->where('student_id',$student_id);
        $this->db->update('students');
        
        
        $this->db->set('amount',$this->input->post('rev_amount'));
        $this->db->set('student_id',$student_id);
        $this->db->set('type','Disount on Admission');
        $this->db->set('comment',$this->input->post('comment'));
        $this->db->set('removed_by',$this->session->userdata('name'));
        $this->db->insert('discount_removals');
        
        $this->session->set_flashdata('message', 'Discount on admission removed successfully.');
        redirect(site_url().'/students/all_discount_details/'.$student_id);
    }
    
    public function remove_special_discount($student_id)
    {
        //GET STUDENT DETAILS
        $this_student = $this->db->get_where('students', array('student_id'=>$this->uri->segment(3)))->result_array();
        
        $this->db->where('id',$this->input->post('rev_id'));
        $this->db->delete('discounts_approval');
        
        $total_fee = $this_student[0]['total_fee']+$this->input->post('rev_amount');
        
        $this->db->set('total_fee',$total_fee);
        $this->db->where('student_id',$student_id);
        $this->db->update('students');
        
        $this->db->set('amount',$this->input->post('rev_amount'));
        $this->db->set('student_id',$student_id);
        $this->db->set('type','Special Discount');
        $this->db->set('comment',$this->input->post('comment'));
        $this->db->set('removed_by',$this->session->userdata('name'));
        $this->db->insert('discount_removals');
        
        $this->session->set_flashdata('message', 'Special Discount removed successfully.');
        redirect(site_url().'/students/all_discount_details/'.$student_id);
    }
    
    public function setTotalFees()
    {
        $students = $this->db->get('students')->result_array();
        
        foreach($students as $student)
        {
            $student_fee_plan = $this->db->get_where('fee_rules',array('fee_rule_id'=>$student['plan_id']))->result_array();
            if(count($student_fee_plan)>0)
            {
                $current_session_fee = $student_fee_plan[0]['total_fee'];
            }
            else
            {
                $class = $this->db->get_where('classes',array('class_id'=>$student['class_id']))->result_array();
                $current_session_fee = $this->db->get_where('fee_rules',array('course_id'=>$student['course_id']))->row()->total_fee;
            }
            
            $this->db->set('current_session_fee',$current_session_fee);
            $this->db->where('student_id',$student['student_id']);
            $this->db->update('students');
        }
    }
    
    public function regenerate_council_fee($student_id,$fee_id)
    {
        $fee = $this->db->get_where('payments',"id = ".$fee_id)->row_array();
        $fee_sequence = $this->db->get_where('council_sequence',"council_sequence_id = ".$fee['council_sequence_id'])->row_array();
        $exam_sequence = $this->db->get_where('exam_sequence',"id = ".$fee['exam_sequence_id'])->row_array();
        
        
                $today = date('Y-m-d');
    
                $student_fee_plan = $this->db->order_by("from_date","ASC")
                    ->where('sequence_fee_id', $fee['council_sequence_id'])
                    ->where('exam_sequence_id', $fee['exam_sequence_id'])
                    ->where('to_date >=', $today)
                    ->get('council_sequence_fee_rules')
                    ->result_array();
                
                if(count($student_fee_plan)>0)
                {
                    $this->db->set('dead_line',$student_fee_plan[0]['to_date']);
                    $this->db->set('amount',$student_fee_plan[0]['exam_fee']);
                    $this->db->where("id",$fee_id);
                    $this->db->update('payments');
                    $this->session->set_flashdata('message', 'Fee Generated Successfully!');
                    redirect('students/payments_paid/'.$student_id);
                }
                else
                {
                    $this->session->set_flashdata('error', 'No Fee Rule Available for '.$fee_sequence['type_name'].' | Exam No : '.$exam_sequence['first_year']);
                    redirect('students/payments_paid/'.$student_id);
                }
            
    }
    
    public function get_council_date($student_id,$fee_id)
    {
        $fee = $this->db->get_where('payments',"id = ".$fee_id)->row_array();
        $student_fee_plan = $this->db->order_by("from_date","ASC")
                    ->where('sequence_fee_id', $fee['council_sequence_id'])
                    ->where('exam_sequence_id', $fee['exam_sequence_id'])
                    ->where('to_date >=', $fee['dead_line'])
                    ->get('council_sequence_fee_rules')
                    ->row_array();
        
        
        echo json_encode([

        "status" => true,

        "to_date" => $student_fee_plan ? $student_fee_plan['to_date'] : ""

    ]);
            
    }
    
    public function college_email_detail()
    {
        $student_id = $this->input->post('student_id');
    
        $student = $this->db->get_where('students', [
            'student_id' => $student_id
        ])->row_array();
    
        if (!$student) {
            echo '<div class="alert alert-danger">Student not found.</div>';
            return;
        }
    
        echo '<p><b>Name:</b> '.$student['first_name'].' '.$student['last_name'].'</p>';
        echo '<p><b>CNIC:</b> '.$student['cnic'].'</p>';
    
        if (!empty($student['college_email'])) {
            echo '<div class="alert alert-success">';
            echo '<p><b>Email:</b> '.$student['college_email'].'</p>';
            echo '<p><b>Password:</b> '.$student['college_email_password'].'</p>';
            echo '<p><b>Login URL:</b> <a href="https://shahbazcollegeofpharmacy.edu.pk/webmail" target="_blank">https://shahbazcollegeofpharmacy.edu.pk/webmail</a></p>';
            echo '<hr>';
            echo '<p><b>How to Login:</b></p>';
            echo '<p>Student Should open webmail URL,Enter full email aur password.</p>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning">College email Not generated.</div>';
            echo '<button type="button" 
                        id="generateCollegeEmailBtn" 
                        data-student-id="'.$student_id.'" 
                        class="btn green">
                        Generate Email
                  </button>';
        }
    }
    
    public function generate_college_email()
    {
        $student_id = $this->input->post('student_id');
    
        $student = $this->db->get_where('students', [
            'student_id' => $student_id
        ])->row_array();
    
        if (!$student) {
            echo '<div class="alert alert-danger">Student not found.</div>';
            return;
        }
    
        if (!empty($student['college_email'])) {
            $this->college_email_detail();
            return;
        }
    
        $domain = 'shahbazcollegeofpharmacy.edu.pk';
    
        // CNIC ko email username banana
        $email_user = trim($student['cnic']);
        $email_user = str_replace(' ', '', $email_user);
    
        if ($email_user == '') {
            echo '<div class="alert alert-danger">Student CNIC empty.</div>';
            return;
        }
    
        $college_email = $email_user . '@' . $domain;
    
        // Password auto generate
        $password = 'Scp@' . rand(100000, 999999);
    
        $cpanel_user = 'shahbazc';
        $cpanel_token = 'BGXBLN16I01QL5TTVCW4JH30S5LTI958';
    
        $url = 'https://shahbazcollegeofpharmacy.edu.pk:2083/execute/Email/add_pop';
    
        $postData = [
            'email'    => $email_user,
            'password' => $password,
            'domain'   => $domain,
            'quota'    => 50
        ];
    
        $curl = curl_init();
    
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HTTPHEADER => [
                'Authorization: cpanel '.$cpanel_user.':'.$cpanel_token,
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
    
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
    
        if ($error) {
            echo '<div class="alert alert-danger">cURL Error: '.$error.'</div>';
            return;
        }
    
        $result = json_decode($response, true);
    
        if (isset($result['status']) && $result['status'] == 1) {
    
            $this->db->where('student_id', $student_id);
            $this->db->update('students', [
                'college_email' => $college_email,
                'college_email_password' => $password
            ]);
    
            echo '<div class="alert alert-success">';
            echo '<h4>Email Generated Successfully</h4>';
            echo '<p><b>Email:</b> '.$college_email.'</p>';
            echo '<p><b>Password:</b> '.$password.'</p>';
            echo '<p><b>Login URL:</b> <a href="https://shahbazcollegeofpharmacy.edu.pk/webmail" target="_blank">https://shahbazcollegeofpharmacy.edu.pk/webmail</a></p>';
            echo '<hr>';
            echo '<p><b>How to Login:</b></p>';
            echo '<p>Student webmail URL open kare:</p>';
            echo '<p><b>Username:</b> '.$college_email.'</p>';
            echo '<p><b>Password:</b> '.$password.'</p>';
            echo '</div>';
    
        } else {
            echo '<div class="alert alert-danger">';
            echo '<b>Email create nahi hui.</b><br>';
            echo '<pre>'.htmlspecialchars($response).'</pre>';
            echo '</div>';
        }
    }
    
    public function update_current_session_fee($student_id)
    {
        $current_session_fee = $this->input->post('current_session_fee');
        $this_student = $this->db->get_where('students', array('student_id'=>$student_id))->row_array();
    
        if ($student_id && $current_session_fee !== '') {
            $this->db->where('student_id', $student_id);
            $this->db->update('students', array(
                'extra_added_fee' => $current_session_fee-$this_student['current_session_fee']
            ));
    
            $this->session->set_userdata('message', 'Current session fee updated successfully.');
        } else {
            $this->session->set_userdata('error', 'Invalid fee amount.');
        }
    
        redirect($_SERVER['HTTP_REFERER']);
    }
}