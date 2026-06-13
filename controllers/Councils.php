<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Councils extends CI_Controller {
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('expense');
        $this->load->library('upload');
        $this->load->library('user_agent');
        require_once("vendor/autoload.php");
    }
    
    public function add_council()
    {
        $data['courses'] = $this->db->get('courses')->result_array();
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/add_council',$data);
        $this->load->view('inc/footer');
    }
    
    public function insert()
    {
        $name = $this->input->post('name');
        $code = $this->input->post('code');
        $phone = $this->input->post('phone');
        $address = $this->input->post('address');
        $location = $this->input->post('location');
        $comment = $this->input->post('comment');
        $course_ids = implode(',',$this->input->post('course_ids'));
        
        $this->db->set('name',$name);
        $this->db->set('code',$code);
        $this->db->set('phone',$phone);
        $this->db->set('address',$address);
        $this->db->set('location',$location);
        $this->db->set('comment',$comment);
        $this->db->set('course_ids',$course_ids);
        $this->db->insert('councils');
        
        $this->session->set_flashdata('message', 'Council added successfully.');
        redirect('councils/add_council');
    }
    
    public function all_councils()
    {
        $data['councils'] = $this->db->get('councils')->result_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/all_councils',$data);
        $this->load->view('inc/footer');
    }
    
    public function edit_council($council_id)
    {
        $data['courses'] = $this->db->get('courses')->result_array();
        
        $data['council'] = $this->db->get_where('councils',array('council_id'=>$council_id))->result_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/edit_council',$data);
        $this->load->view('inc/footer');
    }
    
    public function update($council_id)
    {
        $name = $this->input->post('name');
        $code = $this->input->post('code');
        $phone = $this->input->post('phone');
        $address = $this->input->post('address');
        $location = $this->input->post('location');
        $comment = $this->input->post('comment');
        $course_ids = implode(',',$this->input->post('course_ids'));
        
        $this->db->set('name',$name);
        $this->db->set('code',$code);
        $this->db->set('phone',$phone);
        $this->db->set('address',$address);
        $this->db->set('location',$location);
        $this->db->set('comment',$comment);
        $this->db->set('course_ids',$course_ids);
        $this->db->where('council_id',$council_id);
        $this->db->update('councils');
        
        $this->session->set_flashdata('message', 'Council updated successfully.');
        redirect('councils/edit_council/'.$council_id);
    }
    
    public function delete($council_id)
    {
        $this->db->where('council_id',$council_id);
        $this->db->delete('councils');
        
        $this->session->set_flashdata('message', 'Council deleted successfully.');
        redirect('councils/all_councils');
    }
    
    public function add_council_exams()
    {
        $data['courses'] = $this->db->get('courses')->result_array();
        $data['paper_types'] = $this->db->get('paper_types')->result_array();
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/add_council_exams',$data);
        $this->load->view('inc/footer');
    }
    
    public function insert_council_exam()
    {
        $course_id = $this->input->post('course_id');
        $subject_ids = implode(',',$this->input->post('subject_ids'));
        $paper_type_id = $this->input->post('paper_type_id');
        $paper_name = $this->input->post('paper_name');
        $paper_no = $this->input->post('paper_no');
        $passing_marks = $this->input->post('passing_marks');
        $passing_percentage = $this->input->post('passing_percentage');
        
        
        $this->db->set('course_id',$course_id);
        $this->db->set('subject_ids',$subject_ids);
        $this->db->set('paper_type_id',$paper_type_id);
        $this->db->set('paper_name',$paper_name);
        $this->db->set('paper_no',$paper_no);
        $this->db->set('passing_marks',$passing_marks);
        $this->db->set('passing_percentage',$passing_percentage);
        $this->db->insert('council_exams');
        
        $this->session->set_flashdata('message', 'Council exam added successfully.');
        redirect('councils/add_council_exams');
    }
    
    public function all_council_exams()
    {
        $this->db->select('council_exams.*,paper_types.name as paper_type_name,courses.course_name');
        $this->db->from('council_exams');
        $this->db->join('paper_types','paper_types.paper_type_id=council_exams.paper_type_id','INNER');
        $this->db->join('courses','courses.course_id=council_exams.course_id','INNER');
        $data['papers'] = $this->db->get()->result_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/all_council_exams',$data);
        $this->load->view('inc/footer');
    }
    
    public function edit_council_exam($council_exam_id)
    {
        $data['courses'] = $this->db->get('courses')->result_array();
        $data['paper_types'] = $this->db->get('paper_types')->result_array();
        $data['council_exam'] = $this->db->get_where('council_exams',array('council_exam_id'=>$council_exam_id))->result_array();
        $data['subjects'] = $this->db->get_where('course_subjects',array('course_id'=>$data['council_exam'][0]['course_id']))->result_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/edit_council_exam',$data);
        $this->load->view('inc/footer');
    }
    
    public function update_council_exam($council_exam_id)
    {
        $course_id = $this->input->post('course_id');
        $subject_ids = implode(',',$this->input->post('subject_ids'));
        $paper_type_id = $this->input->post('paper_type_id');
        $paper_name = $this->input->post('paper_name');
        $paper_no = $this->input->post('paper_no');
        $passing_marks = $this->input->post('passing_marks');
        $passing_percentage = $this->input->post('passing_percentage');
        
        
        $this->db->set('course_id',$course_id);
        $this->db->set('subject_ids',$subject_ids);
        $this->db->set('paper_type_id',$paper_type_id);
        $this->db->set('paper_name',$paper_name);
        $this->db->set('paper_no',$paper_no);
        $this->db->set('passing_marks',$passing_marks);
        $this->db->set('passing_percentage',$passing_percentage);
        $this->db->where('council_exam_id',$council_exam_id);
        $this->db->update('council_exams');
        
        $this->session->set_flashdata('message', 'Council exam updated successfully.');
        redirect('councils/edit_council_exam/'.$council_exam_id);
    }
    
    public function delete_council_exam($council_exam_id)
    {
        $this->db->where('council_exam_id',$council_exam_id);
        $this->db->delete('council_exams');
        
        $this->session->set_flashdata('message', 'Council Exam deleted successfully.');
        redirect('councils/all_council_exams');
    }
    
    public function result_rules()
    {
        $this->db->select('*');
        $this->db->from('council_result_rules');
        $this->db->join('courses','courses.course_id=council_result_rules.course_id', 'INNER');
        $data['rules'] = $this->db->get()->result_array();
        
        $data['courses'] = $this->db->get('courses')->result_array();
        
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/result_rules',$data);
        $this->load->view('inc/footer');
    }
    
    public function insert_result_rules()
    {
        $rule_id = $this->input->post('council_result_rule_id');

        $data = [
        'course_id' => $this->input->post('course_id'),
        'total_chances' => $this->input->post('total_chances'),
        'after_chances' => $this->input->post('after_chances'),
        'attempt_scope' => $this->input->post('attempt_scope'),
        'annual_students_can_appear_in' => $this->input->post('annual_students_can_appear_in'),
        'supplementary_students_can_appear_in' => $this->input->post('supplementary_students_can_appear_in'),
        'promote_on_supplementary' => $this->input->post('promote_on_supplementary')
        ];
        
        if($rule_id){
        
        $this->db->where('council_result_rule_id',$rule_id);
        $this->db->update('council_result_rules',$data);
        
        }else{
        
        $this->db->insert('council_result_rules',$data);
        
        }
        
        $this->session->set_flashdata('message', 'Council result rule added successfully.');
        redirect('councils/result_rules');
    }
    
    public function edit_council_result_rule($council_result_rule_id)
    {
        $this->db->select('*');
        $this->db->from('council_result_rules');
        $this->db->join('courses','courses.course_id=council_result_rules.course_id', 'INNER');
        $data['rules'] = $this->db->get()->result_array();
        
        $data['courses'] = $this->db->get('courses')->result_array();
        
        $data['council_result_rule'] = $this->db->get_where('council_result_rules',array('council_result_rule_id'=>$council_result_rule_id))->result_array();
        
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/edit_result_rules',$data);
        $this->load->view('inc/footer');
    }
    
    public function delete_council_result_rule($council_result_rule_id)
    {
        $this->db->where('council_result_rule_id',$council_result_rule_id);
        $this->db->delete('council_result_rules');
        
        $this->session->set_flashdata('message', 'Council result rule added successfully.');
        redirect('councils/result_rules');
    }
    
    public function sequence()
    {
        $data['councils'] = $this->db->get('councils')->result_array();
        $exp_campuses = $this->db->get_where('access', array('user_id'=>$this->session->userdata('user_id')))->row()->expense_campus_ids;

        $this->db->select('*');
        $this->db->from('campuses');
        if($this->session->userdata('role') != 'Admin' && $this->session->userdata('role') != 'Accounts' )
        {
            $this->db->where_in('campus_id', explode(',',$exp_campuses));
        }

        $data['campuses'] = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('campuses');

        $data['allcampuses'] = $this->db->get()->result_array();
        $data['exp_categories'] = $this->db->get_where('expense_category', "sub_of is NULL")->result_array();
        $data['categories'] = $this->expense->getCategories();
        
        $this->db->select('council_sequence.*,courses.course_name,councils.name as council_name');
        $this->db->from('council_sequence');
        $this->db->join('courses','courses.course_id=council_sequence.course_id','INNER');
        $this->db->join('councils','councils.council_id=council_sequence.council_id','INNER');
        $data['sequences'] = $this->db->get()->result_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/sequence',$data);
        $this->load->view('inc/footer');
    }
    
    public function getCouncilCourses()
    {
        $council_id = $this->input->post('council_id');
        
        $course_ids = explode(',',$this->db->get_where('councils',array('council_id'=>$council_id))->row()->course_ids);
        
        $this->db->where_in('course_id',$course_ids);
        $courses = $this->db->get('courses')->result_array();
        
        $html = '';
        
        foreach($courses as $course)
        {
            $html.='<option value="'.$course['course_id'].'">'.$course['course_name'].'</option>';
        }
        
        echo $html;
    }
    
    public function insert_sequence()
    {
        $data = $this->input->post();
        $counter = count($this->input->post('type_name'));
        for($i=0;$i<$counter;$i++)
        {
            $this->db->set('council_id',$data['council_id']);
            $this->db->set('course_id',$data['course_id']);
            $this->db->set('type_name',$data['type_name'][$i]);
            $this->db->set('last_date',$data['date'][$i]);
            $this->db->set('fee',$data['fee'][$i]);
            $this->db->set('expense_date',$data['expense_date'][$i]);
            $this->db->set('expense_fee',$data['expense_fee'][$i]);
            $this->db->set('recurring',$data['recurring'][$i]);
            $this->db->set('has_expense',$data['have_expense'][$i]);
            $this->db->set('action_type',$data['action_type'][$i]);
            $this->db->set('no_of_chances',$data['no_of_chances']);
            if($data['fee'][$i]>0)
            {
                $this->db->set('has_fee',1);
            }
            else
            {
                $this->db->set('has_fee',0);
            }
            if($data['have_expense'][$i]){
                $this->db->set('exp_category_id',$data['expense_category_id'][count($data['expense_category_id'])-1]);
            }else
                $this->db->set('exp_category_id',null);
            $this->db->insert('council_sequence');
        }
        
        $this->session->set_flashdata('message', 'Council sequence added successfully.');
        redirect('councils/sequence');
    }
    
    public function edit_sequence($council_sequence_id)
    {
        $data['councils'] = $this->db->get('councils')->result_array();
        $data['courses'] = $this->db->get('courses')->result_array();
        $data['exp_categories'] = $this->db->get_where('expense_category', "sub_of is NULL")->result_array();
        $data['categories'] = $this->expense->getCategories();

        $this->db->select('council_sequence.*,courses.course_name,councils.name as council_name');
        $this->db->from('council_sequence');
        $this->db->join('courses','courses.course_id=council_sequence.course_id','INNER');
        $this->db->join('councils','councils.council_id=council_sequence.council_id','INNER');
        $data['sequences'] = $this->db->get()->result_array();
        $data['council_sequence'] = $this->db->get_where('council_sequence',array('council_sequence_id'=>$council_sequence_id))->result_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/edit_sequence',$data);
        $this->load->view('inc/footer');
    }

    public function update_sequence($council_sequence_id)
    {
        $data = $this->input->post();
        $this->db->set('council_id',$this->input->post('council_id'));
        $this->db->set('course_id',$this->input->post('course_id'));
        $this->db->set('type_name',$this->input->post('type_name'));
        $this->db->set('last_date',$this->input->post('date'));
        $this->db->set('fee',$this->input->post('fee'));
        if($this->input->post('fee')>0)
        {
            $this->db->set('has_fee',1);
        }
        else
        {
            $this->db->set('has_fee',0);
        }
        $this->db->set('has_expense',$this->input->post('have_expense'));
        $this->db->set('expense_date',$this->input->post('expense_date'));
        $this->db->set('expense_fee',$this->input->post('expense_fee'));
        $this->db->set('recurring',$this->input->post('recurring'));
        $this->db->set('action_type',$this->input->post('action_type'));
            $this->db->set('no_of_chances',$this->input->post('no_of_chances'));
        if($data['have_expense']){
                $this->db->set('exp_category_id',$data['expense_category_id'][count($data['expense_category_id'])-1]);
            }
        $this->db->where("council_sequence_id", $council_sequence_id);
        $this->db->update('council_sequence');

        redirect('councils/sequence');
    }
    
    public function delete_sequence($council_sequence_id)
    {
        $has_rules = $this->db->get_where('council_sequence_fee_rules','sequence_fee_id = '.$council_sequence_id)->result_array();
        if($has_rules){
            $this->session->set_userdata('error', 'This Sequence has Fee Rules deleted them first.');
            redirect('councils/sequence');
        }
        
        $this->db->where('council_sequence_id',$council_sequence_id);
        $this->db->delete('council_sequence');
        
        $this->session->set_flashdata('message', 'Sequence deleted successfully.');
        redirect('councils/sequence');
    }
    
    // public function reports()
    // {
    //     //$data['councils'] = $this->db->get('councils')->result_array();
        
    //     $this->db->select('exam_sequence.*,courses.course_name');
    //     $this->db->from('exam_sequence');
    //     $this->db->join('courses','courses.course_id=exam_sequence.course_id','inner');
    //     $data['council_exams'] = $this->db->get()->result_array();
        
    //     $this->load->view('inc/header');
    //     $this->load->view('inc/sidebar');
    //     $this->load->view('councils/reports',$data);
    //     $this->load->view('inc/footer');
    // }
    
    public function getSubjects()
    {
        $course_id = $this->input->post('course_id');
        $subjects = $this->db->get_where('course_subjects',array('course_id'=>$course_id))->result_array();
        
        $html='';
        foreach($subjects as $subject)
        {
            $html.='<option value="'.$subject['course_subject_id'].'">'.$subject['subject_name'].'</option>';
        }
        echo $html;
    }
    
    public function council_exam_sequence($id = NULL)
    {
        if (!is_dir(getcwd().'/exam_sequence_documents')) {
            mkdir(getcwd().'/exam_sequence_documents', 0777);
        }
        if (@$this->input->post('type') != "")
        {
            $this->db->set("type",$this->input->post('type'));
            $this->db->set("first_year",$this->input->post('first_year'));
            $this->db->set("second_year",$this->input->post('second_year'));
            if (@$this->input->post('seq_id')) {

                //load the helper
                $this->load->helper('form');
                //Configure
                //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
                $config['upload_path'] = 'exam_sequence_documents/';
                // set the filter image types
                $config['allowed_types'] = 'csv';
                //load the upload library
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                $this->upload->set_allowed_types('*');
                $data['upload_data'] = '';

                //if not successful, set the error message
                if (!$this->upload->do_upload('first_year_roll_no')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $first_year_roll_no = $this->input->post('old_first_year_roll_no');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $first_year_roll_no = $data['upload_data']['file_name'];
                    }
                }
                
                //if not successful, set the error message
                if (!$this->upload->do_upload('first_year_date_sheet')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $first_year_date_sheet = $this->input->post('old_first_year_date_sheet');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $first_year_date_sheet = $data['upload_data']['file_name'];
                    }
                }
                
                //if not successful, set the error message
                if (!$this->upload->do_upload('first_year_date_sheet_nts')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $first_year_date_sheet_nts = $this->input->post('old_first_year_date_sheet_nts');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $first_year_date_sheet_nts = $data['upload_data']['file_name'];
                    }
                }
                
                //if not successful, set the error message
                if (!$this->upload->do_upload('first_year_result')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $first_year_result = $this->input->post('old_first_year_result');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $first_year_result = $data['upload_data']['file_name'];
                    }
                }
                
                //if not successful, set the error message
                if (!$this->upload->do_upload('second_year_roll_no')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $second_year_roll_no = $this->input->post('old_second_year_roll_no');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $second_year_roll_no = $data['upload_data']['file_name'];
                    }
                }
                
                //if not successful, set the error message
                if (!$this->upload->do_upload('second_year_date_sheet')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $second_year_date_sheet = $this->input->post('old_second_year_date_sheet');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $second_year_date_sheet = $data['upload_data']['file_name'];
                    }
                }
                
                //if not successful, set the error message
                if (!$this->upload->do_upload('second_year_date_sheet_nts')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $second_year_date_sheet_nts = $this->input->post('old_second_year_date_sheet_nts');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $second_year_date_sheet_nts = $data['upload_data']['file_name'];
                    }
                }
                
                //if not successful, set the error message
                if (!$this->upload->do_upload('second_year_result')) {
                    $data = array('msg' => $this->upload->display_errors());
                    $second_year_result = $this->input->post('old_second_year_result');
                }
                else
                {
                    //else, set the success message
                    $data['upload_data'] = $this->upload->data();
                    if($data['upload_data']['file_name']){
                        $second_year_result = $data['upload_data']['file_name'];
                    }
                }

                $this->db->set("first_year_roll_no",$first_year_roll_no);
                $this->db->set("first_year_date_sheet",$first_year_date_sheet);
                $this->db->set("first_year_date_sheet_nts",$first_year_date_sheet_nts);
                $this->db->set("first_year_result",$first_year_result);
                $this->db->set("second_year_roll_no",$second_year_roll_no);
                $this->db->set("second_year_date_sheet",$second_year_date_sheet);
                $this->db->set("second_year_date_sheet_nts",$second_year_date_sheet_nts);
                $this->db->set("second_year_result",$second_year_result);

                $this->db->where("id", $this->input->post('seq_id'));
                $this->db->update("exam_sequence");
            }
            else {
                $this->db->insert("exam_sequence");
            }
        }

        //$data['sequences'] = $this->db->get("exam_sequence")->result_array();
        $this->db->select('exam_sequence.*,courses.course_name');
        $this->db->from('exam_sequence');
        $this->db->join('courses','courses.course_id=exam_sequence.course_id');
        if($id){
            $this->db->where('exam_sequence.status',$id);
        }
        $data['sequences'] = $this->db->get()->result_array();
        
        $data['courses'] = $this->db->get('courses')->result_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/council_exam_sequence',$data);
        $this->load->view('inc/footer');
    }
    
    public function edit_council_exam_sequence($id)
    {
        $data['seq'] = $this->db->get_where("exam_sequence","id = $id")->row();
        
        $data['courses'] = $this->db->get('courses')->result_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/edit_council_exam_sequence',$data);
        $this->load->view('inc/footer');
    }
    
    public function update_exam_sequence()
    {
        if (@$this->input->post('seq_id')) {

            //load the helper
            $this->load->helper('form');
            //Configure
            //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
            $config['upload_path'] = 'exam_sequence_documents/';
            // set the filter image types
            $config['allowed_types'] = 'csv';
            //load the upload library
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            $this->upload->set_allowed_types('*');
            $data['upload_data'] = '';

            //if not successful, set the error message
            if (!$this->upload->do_upload('first_year_roll_no')) {
                $data = array('msg' => $this->upload->display_errors());
                $first_year_roll_no = $this->input->post('old_first_year_roll_no');
            }
            else
            {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if($data['upload_data']['file_name']){
                    $first_year_roll_no = $data['upload_data']['file_name'];
                }
            }
            //if not successful, set the error message
            if (!$this->upload->do_upload('first_year_date_sheet')) {
                $data = array('msg' => $this->upload->display_errors());
                $first_year_date_sheet = $this->input->post('old_first_year_date_sheet');
            }
            else
            {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if($data['upload_data']['file_name']){
                    $first_year_date_sheet = $data['upload_data']['file_name'];
                }
            }
            //if not successful, set the error message
            if (!$this->upload->do_upload('first_year_date_sheet_nts')) {
                $data = array('msg' => $this->upload->display_errors());
                $first_year_date_sheet_nts = $this->input->post('old_first_year_date_sheet_nts');
            }
            else
            {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if($data['upload_data']['file_name']){
                    $first_year_date_sheet_nts = $data['upload_data']['file_name'];
                }
            }
            //if not successful, set the error message
            if (!$this->upload->do_upload('first_year_result')) {
                $data = array('msg' => $this->upload->display_errors());
                $first_year_result = $this->input->post('old_first_year_result');
            }
            else
            {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if($data['upload_data']['file_name']){
                    $first_year_result = $data['upload_data']['file_name'];
                }
            }
            //if not successful, set the error message
            if (!$this->upload->do_upload('second_year_roll_no')) {
                $data = array('msg' => $this->upload->display_errors());
                $second_year_roll_no = $this->input->post('old_second_year_roll_no');
            }
            else
            {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if($data['upload_data']['file_name']){
                    $second_year_roll_no = $data['upload_data']['file_name'];
                }
            }
            //if not successful, set the error message
            if (!$this->upload->do_upload('second_year_date_sheet')) {
                $data = array('msg' => $this->upload->display_errors());
                $second_year_date_sheet = $this->input->post('old_second_year_date_sheet');
            }
            else
            {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if($data['upload_data']['file_name']){
                    $second_year_date_sheet = $data['upload_data']['file_name'];
                }
            }
            //if not successful, set the error message
            if (!$this->upload->do_upload('second_year_date_sheet_nts')) {
                $data = array('msg' => $this->upload->display_errors());
                $second_year_date_sheet_nts = $this->input->post('old_second_year_date_sheet_nts');
            }
            else
            {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if($data['upload_data']['file_name']){
                    $second_year_date_sheet_nts = $data['upload_data']['file_name'];
                }
            }
            //if not successful, set the error message
            if (!$this->upload->do_upload('second_year_result')) {
                $data = array('msg' => $this->upload->display_errors());
                $second_year_result = $this->input->post('old_second_year_result');
            }
            else
            {
                //else, set the success message
                $data['upload_data'] = $this->upload->data();
                if($data['upload_data']['file_name']){
                    $second_year_result = $data['upload_data']['file_name'];
                }
            }

            $course_id = $this->input->post('course_id');
            //FIRST YEAR VALUES
            $first_year_type = $this->input->post('first_year_type');
            $first_year = $this->input->post('first_year');
            $first_year_expected_roll_no = $this->input->post('exam_year');
            // $first_year_expected_exam = $this->input->post('first_year_expected_exam');
            // $first_year_expected_result = $this->input->post('first_year_expected_result');
            
            // //SECOND YEAR VALUES
            // $second_year_type = $this->input->post('second_year_type');
            // $second_year = $this->input->post('second_year');
            // $second_year_expected_roll_no = $this->input->post('second_year_expected_roll_no');
            // $second_year_expected_exam = $this->input->post('second_year_expected_exam');
            // $second_year_expected_result = $this->input->post('second_year_expected_result');
            
            
            $this->db->set('course_id',$course_id);
            //FIRST YEAR VALUES
            $this->db->set('first_year_type',$first_year_type);
            $this->db->set('first_year',$first_year);
            $this->db->set('class',$first_year_expected_roll_no);
            
            //SECOND YEAR VALUES
            // $this->db->set('second_year_type',$second_year_type);
            // $this->db->set('second_year',$second_year);
            // $this->db->set('second_year_expected_roll_no',$second_year_expected_roll_no);
            // $this->db->set('second_year_expected_exam',$second_year_expected_exam);
            // $this->db->set('second_year_expected_result',$second_year_expected_result);

            $this->db->where("id", $this->input->post('seq_id'));
            $this->db->update("exam_sequence");
        }
        $this->session->set_flashdata('message', 'Exam Sequence Updated Successfully.');
        redirect('councils/council_exam_sequence');
    }
    
    public function insert_exam_sequence()
    {
        $course_id = $this->input->post('course_id');
        $first_year_type = $this->input->post('first_year_type');
        $first_year = $this->input->post('first_year');
        $first_year_expected_roll_no = $this->input->post('exam_year');
    
        // Check Existing Record
        $exists = $this->db
            ->where('course_id', $course_id)
            ->where('class', $first_year_expected_roll_no)
            ->where('first_year_type', $first_year_type)
            ->where('first_year', $first_year)
            ->get('exam_sequence')
            ->row_array();
    
        if($exists)
        {
            $this->session->set_flashdata('error', 'Exam Sequence Already Created and Status is '.$exists['status']);
            redirect('councils/council_exam_sequence');
            return;
        }
    
        $data = array(
            'course_id'       => $course_id,
            'first_year_type' => $first_year_type,
            'first_year'      => $first_year,
            'class'           => $first_year_expected_roll_no
        );
    
        $this->db->insert('exam_sequence', $data);
    
        $this->session->set_flashdata('message', 'Exam Sequence Added Successfully.');
        redirect('councils/council_exam_sequence');
    }
    
    public function students($course_id,$session,$exam_no,$class,$type=null,$sequence_id=null)
    {
        if($class==1)
        {
            $thisClass = '1st';
        }
        else
        {
            $thisClass = '2nd';
        }
        $this->db->select('students.*,campuses.campus_name,classes.session,classes.name as class_name,payments.council_sequence_id,payments.paid');
        $this->db->from('payments');
        $this->db->join('students','students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)','inner');
        $this->db->join('classes','classes.class_id=students.class_id','inner');
        $this->db->join('campuses','campuses.campus_id=classes.campus_id','left');
        if($session != 0)
            $this->db->where('classes.session',$session);
        $this->db->where('students.status',1);
        $this->db->where('students.course_id',$course_id);
        $this->db->like('payments.payment_comment','This fee for next exam # '.$exam_no.' '.$thisClass.' Year','both');
        // if($type == 'paid')
        //     $this->db->where('payments.paid',1);
        if($type == 'unpaid')
            $this->db->where('payments.paid',0);
        if($type && $type != "fee_not_created"){
            $this->db->where('payments.council_sequence_id',$sequence_id);
        }
        
        $this->db->group_by('students.student_id');
        $this->db->order_by('students.status ASC, payments.paid DESC');
        $data['students'] = $this->db->get()->result_array();
        if($type && $type == "fee_not_created"){
            foreach($data['students'] as $key=>$student){
                $this->db->select('payments.council_sequence_id');
                $this->db->from('payments');
                $this->db->like('payments.payment_comment','This fee for next exam # '.$exam_no.' '.$thisClass.' Year','both');
                $this->db->where(
    'COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id) =',
    $student['student_id']
);
                $student_fees = $this->db->get()->result_array();
                foreach($student_fees as $fee)
                if($fee['council_sequence_id'] == $sequence_id){
                    unset($data['students'][$key]);
                }
            }
        }
        $data['council_ids'] = $this->db->get_where("bank_reconciliation_statement","is_council_fee = 1 and CAST(REPLACE(debit,',','') as SIGNED) > tagged_amount")->result_array();
        $this->db->select('*');
        $this->db->from('petty_cash_college_wise');
        $this->db->where('assign_to', $this->session->userdata('user_id'));
        $query = $this->db->get()->result_array();

        if(count($query)>0){
            $data['pettycash']=my_pettycash();
        }else {
            $data['pettycash']=0;
        }
        $data['course'] = $this->db->get_where('courses','course_id = '.$course_id)->row_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/students',$data);
        $this->load->view('inc/footer');
    }
    
    public function info_students($course_id,$session,$exam_no,$class,$type=null,$sequence_id=null)
    {
        if($class==1)
        {
            $thisClass = '1st';
        }
        else
        {
            $thisClass = '2nd';
        }
        $this->db->select('students.*,campuses.campus_name,classes.session,classes.name as class_name,payments.council_sequence_id');
        $this->db->from('payments');
        $this->db->join('students','students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)','inner');
        $this->db->join('classes','classes.class_id=students.class_id','inner');
        $this->db->join('campuses','campuses.campus_id=classes.campus_id','left');
        $this->db->where('students.status',1);
        $this->db->where('students.course_id',$course_id);
        $this->db->like('payments.payment_comment','This fee for next exam # '.$exam_no.' '.$thisClass.' Year','both');
        $this->db->group_by('students.student_id');
        $data['students'] = $this->db->get()->result_array();
        $data['sequence'] = $this->db->get_where('council_sequence','council_sequence_id = '.$sequence_id)->row_array();
        $data['course'] = $this->db->get_where('courses','course_id = '.$course_id)->row_array();
        
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/info_students',$data);
        $this->load->view('inc/footer');
    }
    
    public function add_expense(){
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
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = $data['upload_data']['file_name'];
            }
        }
        $data = $this->input->post();
        $student_ids = $data['student_ids'];
        $sequence = $this->db->get_where('council_sequence','council_sequence_id = '.$data['council_sequence_id'])->row_array();
        
			foreach($student_ids as $key=>$student_id)
			{
			    $st_detail = $this->db->join('courses', 'courses.course_id = students.course_id')->join('classes', 'classes.class_id = students.class_id')->get_where("students","student_id = '".$student_id."'")->row();
				$this->db->set('campus_id',$st_detail->campus_id);
				$this->db->set('expense_category_id',$sequence['exp_category_id']);
				$this->db->set('title',$st_detail->course_name." / ".@$data['examclass']." / ".$sequence['type_name']." expense for student exam no ".@$data['council_exam_no']);
				$this->db->set('date',$data['expense_date']);
				$this->db->set('amount',$data['expense_amount']);
				$this->db->set('purpose',$st_detail->first_name." ".$st_detail->last_name.'-'.$st_detail->cnic.'-'.$st_detail->mobile);
				$this->db->set('month_year',date('Y-m'));
				$this->db->set('class',@$data['examclass']);
                $this->db->set('council_exam_no',$data['council_exam_no']);
                $this->db->set('council_sequence_id',$data['council_sequence_id']);
				
				
				$this->db->set('student_id',$student_id);
				$this->db->set('actual_date', date('Y-m-d H:i:s'));
				$this->db->set('image', $image);
				$this->db->set('payment_type', $data['payment_type']);
				$this->db->set('class_id', $st_detail->class_id);
				$this->db->set('roll_no', $st_detail->roll_no);
				$this->db->set('add_by', $this->session->userdata('name'));
				$this->db->set('last_edit', $this->session->userdata('name'));
				$this->db->set('add_by_id', $this->session->userdata('user_id'));
				$this->db->set('approved_status', 1);
				if($data['payment_type']=='cash')
				{
					$this->db->set('paid_type', 'cash');
				}
				else
				{
					$this->db->set('paid_type', 'bank');
				}
				$this->db->insert('expenses');
				$insert_id = $this->db->insert_id();
				if ($data['payment_type'] != "cash") {
                    $this->db->set('tagged_amount', 'tagged_amount +' .$data['expense_amount']. '', false);
                    $this->db->set('expense_id',$insert_id);
                    $this->db->where('id', $data['payment_type']);
                    $this->db->update('bank_reconciliation_statement');
                }
			}
		$this->session->set_flashdata('message', 'Exam Sequence Expense Updated Successfully.');
        redirect($this->agent->referrer());
    }
    
    // public function report($status = Null)
    // {
    //     $exp_campuses = $this->db->get_where('access', array('user_id'=>$this->session->userdata('user_id')))->row()->council_report_courses;
        
        
    //     $this->db->select('exam_sequence.*,courses.course_name');
    //     $this->db->from('exam_sequence');
    //     $this->db->join('courses','courses.course_id=exam_sequence.course_id','inner');
    //     if($status){
    //         $this->db->where('exam_sequence.status',$status);
    //     }
        
    //     if($this->session->userdata('role') != 'Admin' && $exp_campuses != '')
    //     {
    //         $this->db->where_in('exam_sequence.course_id', explode(',',$exp_campuses));
    //     }
        
    //     $data['council_exams'] = $this->db->get()->result_array();
        
    //     $this->load->view('inc/header');
    //     $this->load->view('inc/sidebar');
    //     $this->load->view('councils/report',$data);
    //     $this->load->view('inc/footer');
    // }
    
    public function report($status = null)
    {
        $exp_campuses = $this->db->get_where(
            'access',
            array('user_id' => $this->session->userdata('user_id'))
        )->row()->council_report_courses;
    
        $this->db->select('courses.course_id, courses.course_name');
        $this->db->from('courses');
    
        if ($this->session->userdata('role') != 'Admin' && $exp_campuses != '') {
            $this->db->where_in('courses.course_id', explode(',', $exp_campuses));
        }
    
        $data['courses'] = $this->db->get()->result_array();
        $data['status']  = $status;
    
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/report', $data);
        $this->load->view('inc/footer');
    }
    
    public function report_ajax($status = null)
    {
        $exp_campuses = $this->db->get_where(
            'access',
            array('user_id' => $this->session->userdata('user_id'))
        )->row()->council_report_courses;
    
        $this->db->select('exam_sequence.*,courses.course_name,courses.course_type');
        $this->db->from('exam_sequence');
        $this->db->join('courses', 'courses.course_id=exam_sequence.course_id', 'inner');
    
        if ($status) {
            $this->db->where('exam_sequence.status', $status);
        }
    
        if ($this->session->userdata('role') != 'Admin' && $exp_campuses != '') {
            $this->db->where_in('exam_sequence.course_id', explode(',', $exp_campuses));
        }
    
        $data['council_exams'] = $this->db->get()->result_array();
        
    
        $this->load->view('councils/report_ajax', $data);
    }
        
    public function save_informed()
    {
        $council_sequence_id = $this->input->post('council_sequence_id');
        $student_id = $this->input->post('student_id');
        $exam_no = $this->input->post('exam_no');
        $class = $this->input->post('class');
        $course_id = $this->input->post('course_id');
        $comment = $this->input->post('comment');
        $informed = $this->input->post('informed');
        
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
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = $data['upload_data']['file_name'];
            }
        }
    
        $student = $this->db
            ->where('student_id', $student_id)
            ->get('students')
            ->row_array();
    
        $row = $this->db
            ->where('cnic', $student['cnic'])
            ->where('council_exam_no', $exam_no)
            ->where('course_id', $course_id)
            ->where('class', $class)
            ->get('punjab_council_roll_number')
            ->row_array();
    
        $extra_info = array();
        
        $new_info = array(
            'council_sequence_id' => $council_sequence_id,
            'comment' => $comment,
            'informed_by' => $this->session->userdata('name'),
            'informed_at' => date('Y-m-d H:i:s'),
            'informed' => $informed,
            'image_url' => $image
        );
        
        if($row){
    
            if(!empty($row['extra_info'])){
                $extra_info = json_decode($row['extra_info'], true);
            }
        
            if(!is_array($extra_info)){
                $extra_info = array();
            }
        
        
            $updated = false;
        
            // foreach($extra_info as $key => $info)
            // {
            //     if($info['council_sequence_id'] == $council_sequence_id)
            //     {
            //         $extra_info[$key] = $new_info;
            //         $updated = true;
            //     }
            // }
        
            if(!$updated){
                $extra_info[] = $new_info;
            }
        
            $this->db
                ->where('id', $row['id'])
                ->update(
                    'punjab_council_roll_number',
                    array('extra_info' => json_encode($extra_info))
                );
        }else{
            $extra_info[] = $new_info;
            $this->db->set('council_exam_no',$exam_no);
            $this->db->set('class', $class);
            $this->db->set('cnic', $student['cnic']);
            $this->db->set('name', $student['first_name']." ".$student['last_name']);
            $this->db->set('address', $student['address']);
            $this->db->set('course_id', $course_id);
            $this->db->set('extra_info', json_encode($extra_info));
            $this->db->insert('punjab_council_roll_number');
        }
    
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function add_sequence_information()
    {
    
        $data = array();
    
        $data['council_sequenec_id'] = $this->input->post('council_sequence_id');
        $data['general_comment'] = $this->input->post('general_comment');
        $data['created_at'] = date('Y-m-d H:i:s');
    
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

            $data['image'] = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $data['image'] = $data['upload_data']['file_name'];
            }
        }
        unset($data['upload_data']);
    
        $this->db->insert('council_sequence_inform_report',$data);
    
        $this->session->set_flashdata('message','Information Added Successfully');
    
        redirect($_SERVER['HTTP_REFERER']);
    
    }
    
    public function documents_students($course_id,$session,$exam_no,$class,$type=null,$sequence_id=null)
    {
        if($class==1)
        {
            $thisClass = '1st';
        }
        else
        {
            $thisClass = '2nd';
        }
        $exam_no            = $this->uri->segment(5);
        $class              = $this->uri->segment(6);
        $council_sequence_id = $this->uri->segment(8);
        
        $this->db->select('students.*, campuses.campus_name, classes.session, classes.name as class_name, payments.council_sequence_id');
        $this->db->from('payments');
        
        $this->db->join('students', 'students.student_id = payments.student_id', 'inner');
        $this->db->join('classes', 'classes.class_id = students.class_id', 'inner');
        $this->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'left');
        
        $this->db->join(
            'expenses',
            'expenses.student_id = students.student_id
             AND expenses.council_exam_no = '.$this->db->escape($exam_no).'
             AND expenses.council_sequence_id = '.$this->db->escape($council_sequence_id).'
             AND expenses.class = '.$this->db->escape($class),
            'left'
        );
        
        $this->db->where('students.course_id', $course_id);
        $this->db->like('payments.payment_comment', 'This fee for next exam # '.$exam_no.' '.$thisClass.' Year', 'both');
        $this->db->where('(students.status = 1 OR (students.status = 0 AND expenses.expense_id IS NOT NULL))', null, false);
        $this->db->group_by('students.student_id');
        
        $data['students'] = $this->db->get()->result_array();
        $data['sequence'] = $this->db->get_where('council_sequence','council_sequence_id = '.$sequence_id)->row_array();
        $data['course'] = $this->db->get_where('courses','course_id = '.$course_id)->row_array();
        
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/documents_students',$data);
        $this->load->view('inc/footer');
    }
    
    // public function result_students($course_id,$session,$exam_no,$class,$type=null,$sequence_id=null,$exam_sequence=null)
    // {
    //     if($class==1)
    //     {
    //         $thisClass = '1st';
    //     }
    //     else
    //     {
    //         $thisClass = '2nd';
    //     }
    //     $this->db->select('students.*,campuses.campus_name,classes.session,classes.name as class_name,payments.council_sequence_id');
    //     $this->db->from('payments');
    //     $this->db->join('students','students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)','inner');
    //     $this->db->join('classes','classes.class_id=students.class_id','inner');
    //     $this->db->join('campuses','campuses.campus_id=classes.campus_id','left');
    //     $this->db->where('students.status',1);
    //     $this->db->where('students.course_id',$course_id);
    //     $this->db->like('payments.payment_comment','This fee for next exam # '.$exam_no.' '.$thisClass.' Year','both');
    //     $this->db->group_by('students.student_id');
    //     $data['students'] = $this->db->get()->result_array();
    //     $data['sequence'] = $this->db->get_where('council_sequence','council_sequence_id = '.$sequence_id)->row_array();
    //     $data['current_exam_sequence'] = $this->db->get_where('exam_sequence','id = '.$exam_sequence)->row_array();
    //     $data['course'] = $this->db->get_where('courses','course_id = '.$course_id)->row_array();
        
        
    //     $this->load->view('inc/header');
    //     $this->load->view('inc/sidebar');
    //     $this->load->view('councils/result_students',$data);
    //     $this->load->view('inc/footer');
    // }
    
    public function result_students($course_id,$session,$exam_no,$class,$type=null,$sequence_id=null,$exam_sequence=null)
    {
        $thisClass = ($class == 1) ? '1st' : '2nd';
    
        $this->db->select('students.*,campuses.campus_name,classes.session,classes.name as class_name,payments.council_sequence_id');
        $this->db->from('payments');
        $this->db->join('students','students.student_id = COALESCE(NULLIF(payments.student_id, 0), payments.custom_student_id)','inner');
        $this->db->join('classes','classes.class_id=students.class_id','inner');
        $this->db->join('campuses','campuses.campus_id=classes.campus_id','left');
        $this->db->where('students.status',1);
        $this->db->where('students.course_id',$course_id);
        $this->db->like('payments.payment_comment','This fee for next exam # '.$exam_no.' '.$thisClass.' Year','both');
        $this->db->group_by('students.student_id');
        $students = $this->db->get()->result_array();
    
        $data['students'] = $students;
        $data['sequence'] = $this->db->get_where('council_sequence', ['council_sequence_id' => $sequence_id])->row_array();
        $data['current_exam_sequence'] = $this->db->get_where('exam_sequence', ['id' => $exam_sequence])->row_array();
        $data['course'] = $this->db->get_where('courses', ['course_id' => $course_id])->row_array();
    
        // exams yahan preload
        $data['exams'] = $this->db
            ->select('council_exams.*, paper_types.*')
            ->from('council_exams')
            ->join('paper_types', 'paper_types.paper_type_id = council_exams.paper_type_id')
            ->join(
                'course_subjects',
                'FIND_IN_SET(course_subjects.course_subject_id, council_exams.subject_ids) > 0',
                'inner',
                false
            )
            ->where('council_exams.course_id', $course_id)
            ->where('course_subjects.subject_year', $class)
            ->group_by('council_exams.council_exam_id')
            ->get()
            ->result_array();
    
        // roll numbers preload
        $roll_rows = $this->db
            ->where('council_exam_no', $exam_no)
            ->where('class', $class)
            ->where('course_id', $course_id)
            ->get('punjab_council_roll_number')
            ->result_array();
    
        $roll_map = [];
        $roll_ids = [];
    
        foreach ($roll_rows as $row) {
            $roll_map[$row['cnic']] = $row;
            $roll_ids[] = $row['id'];
        }
    
        $data['roll_map'] = $roll_map;
    
        // paper results preload
        $paper_result_map = [];
    
        if (!empty($roll_ids)) {
            $paper_results = $this->db
                ->where_in('punjab_council_roll_number_id', $roll_ids)
                ->get('council_exam_papers_result')
                ->result_array();
    
            foreach ($paper_results as $pr) {
                $key = $pr['punjab_council_roll_number_id'].'_'.$pr['council_exam_id'];
                $paper_result_map[$key] = $pr;
            }
        }
    
        $data['paper_result_map'] = $paper_result_map;
    
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('councils/result_students',$data);
        $this->load->view('inc/footer');
    }
    
    public function save_roll_no()
    {
        $council_sequence_id = $this->input->post('council_sequence_id');
        $student_id = $this->input->post('student_id');
        $exam_no = $this->input->post('exam_no');
        $class = $this->input->post('class');
        $course_id = $this->input->post('course_id');
        $roll_no = $this->input->post('roll_no');
        $save_type = $this->input->post('save_type');
        
        $this->load->helper('form');
        $config['upload_path'] = 'rollno_slips/';
        $config['allowed_types'] = 'gif|jpg|png';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->set_allowed_types('*');
        $data['upload_data'] = '';
        
        if (!$this->upload->do_upload('image')) {
            $image = '';
        }
        else
        {
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $image = 'rollno_slips/'.$data['upload_data']['file_name'];
            }
        }
    
        $student = $this->db
            ->where('student_id', $student_id)
            ->get('students')
            ->row_array();
    
        $row = $this->db
            ->where('cnic', $student['cnic'])
            ->where('council_exam_no', $exam_no)
            ->where('course_id', $course_id)
            ->where('class', $class)
            ->get('punjab_council_roll_number')
            ->row_array();

        if($row){
            $this->db->set('council_exam_no',$exam_no);
            $this->db->set('class', $class);
            $this->db->set('cnic', $student['cnic']);
            $this->db->set('name', $student['first_name']." ".$student['last_name']);
            $this->db->set('address', $student['address']);
            $this->db->set('course_id', $course_id);
            if($save_type == 'roll_no'){
                $this->db->set('roll_no', $roll_no);
                $this->db->set('slip_image', $image);
            }else{
                $this->db->set('result_remarks', $roll_no);
                $this->db->set('result_image', $image);
            }
            $this->db->where('id', $row['id'])
            ->update('punjab_council_roll_number');
        }else{
            $this->db->set('council_exam_no',$exam_no);
            $this->db->set('class', $class);
            $this->db->set('cnic', $student['cnic']);
            $this->db->set('name', $student['first_name']." ".$student['last_name']);
            $this->db->set('address', $student['address']);
            $this->db->set('course_id', $course_id);
            if($save_type == 'roll_no'){
                $this->db->set('roll_no', $roll_no);
                $this->db->set('slip_image', $image);
            }else{
                $this->db->set('result_remarks', $roll_no);
                $this->db->set('result_image', $image);
            }
            $this->db->insert('punjab_council_roll_number');
        }
    
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function save_result()
    {
        $council_exam_id = $this->input->post('council_exam_id');
        $punjab_council_roll_number_id = $this->input->post('punjab_council_roll_number_id');
        $type = $this->input->post('type');
        $result_direct = $this->input->post('result_direct');
        $result_marks = $this->input->post('result_marks');
        
    
        $row = $this->db
            ->where('punjab_council_roll_number_id', $punjab_council_roll_number_id)
            ->where('council_exam_id', $council_exam_id)
            ->get('council_exam_papers_result')
            ->row_array();
            
            $pass_rule = $this->db
            ->where('council_exam_id', $council_exam_id)
            ->get('council_exams')
            ->row_array();
            
            if($type == 'marks'){
                $result_direct = $result_marks >= $pass_rule['passing_marks'] ? 'Pass':'Fail';
            }

        if($row){
            $this->db->set('council_exam_id',$council_exam_id);
            $this->db->set('punjab_council_roll_number_id', $punjab_council_roll_number_id);
            $this->db->set('type', $type);
            $this->db->set('result', $result_direct);
            $this->db->set('marks', $result_marks);
            $this->db->where('id', $row['id'])
            ->update('council_exam_papers_result');
        }else{
            $this->db->set('council_exam_id',$council_exam_id);
            $this->db->set('punjab_council_roll_number_id', $punjab_council_roll_number_id);
            $this->db->set('type', $type);
            $this->db->set('result', $result_direct);
            $this->db->set('marks', $result_marks);
            $this->db->insert('council_exam_papers_result');
        }
    
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function save_fee_rule()
    {
        $fee_rule_id = $this->input->post('fee_rule_id');
        $data = array(
            'sequence_fee_id' => $this->input->post('sequence_fee_id'),
            'exam_sequence_id' => $this->input->post('sequence_exam_fee_id'),
            'from_date' => $this->input->post('from_date'),
            'to_date' => $this->input->post('to_date'),
            'exam_fee' => $this->input->post('exam_fee'),
            'expense_fee' => $this->input->post('expense_fee'),
            'has_first_time_fee' => $this->input->post('has_first_time_fee'),
            'first_time_fee' => $this->input->post('first_time_fee'),
            'first_time_expense' => $this->input->post('first_time_expense')
        );
    
        if (!empty($fee_rule_id)) {

            $this->db->where('id', $fee_rule_id);
    
            $this->db->update('council_sequence_fee_rules', $data);
    
            $this->session->set_userdata('message', 'Fee rule updated successfully.');
    
        } else {
            $this->db->insert('council_sequence_fee_rules',$data);
        }
    
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function delete_fee_rule($id)
    {
        $this->db->where('id',$id);
        $this->db->delete('council_sequence_fee_rules');
    
        $this->session->set_flashdata('message','Fee rule deleted successfully');
    
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function create_fee_for_all()
    {
        $payload = $this->input->post('bulk_fee_payload');
        $payload = json_decode($payload, true);
    
        if (empty($payload) || !is_array($payload)) {
            $this->session->set_userdata('error', 'Invalid fee payload.');
            redirect($_SERVER['HTTP_REFERER']);
        }
        
        
        foreach ($payload as $studentData) {
            $student_id = $studentData['student_id'];
            
    
            if (empty($studentData['fees']) || !is_array($studentData['fees'])) {
                continue;
            }
    
            foreach ($studentData['fees'] as $fee) {
                
                if($fee['type'] == 'Extra fee'){
                    
                    $already = $this->db->get_where('payments', [
                        'student_id' => $student_id,
                        'exam_sequence_id' => $fee['next_exam_sequence_id'],
                        'exam_class' => $fee['class'],
                        'council_sequence_id' => $fee['council_sequence_id']
                    ])->row_array();
        
                    if (!$already) {
                        $dead_line = $fee['dead_line'];
                        $challan_no = $this->getChallanNo();
            
                        $this->db->set('amount', $fee['amount']);
                        $this->db->set('dead_line', $dead_line);
                        $this->db->set('student_id', $student_id);
                        $this->db->set('payment_plan', 'Custom Plan');
                        $this->db->set('payment_comment', $fee['comment']);
                        $this->db->set('challan_no', $challan_no);
                        $this->db->set('exam_class', $fee['class']);
                        $this->db->set('exam_sequence_id' , $fee['next_exam_sequence_id']);
                        $this->db->set('council_sequence_id' , $fee['council_sequence_id']);
                        $this->db->set('add_by', $this->session->userdata('name'));
                        $this->db->set('last_edit', $this->session->userdata('name'));
                        $this->db->insert('payments');
                    }
                    
                }else{
                    $challan_no = $this->getChallanNo();
                
                    $insert = [
                        'student_id' => $student_id,
                        'course_id' => $this->input->post('course_id'),
                    
                        'amount' => $fee['amount'],
                    
                        'exam_class' => $fee['class'],
                        'exam_sequence_id' => $fee['next_exam_sequence_id'],
                        'council_sequence_id' => $fee['council_sequence_id'],
                        'dead_line' => $fee['dead_line'],
                    
                        'paid' => 0,
                        'discount' => 0,
                    
                        'remaining_installment_amount' => 0,
                        'extra_amount' => 0,
                        'shifted_installment' => 0,
                        'removed_previous_fine' => 0,
                        'shifted_previous_fine' => 0,
                        'removed_fine' => 0,
                        'shifted_fine' => 0,
                    
                        'college_fee' => 0,
                        'clear_college_fee' => 0,
                    
                        'paid_challans' => '',
                        'challan_no' => $challan_no,
                        'scan_challan' => '',
                        'online_scan_challan' => '',
                    
                        'upload_scan_challan' => 0,
                        'fine_application' => '',
                        'online_fine_application' => '',
                    
                        'upload_fine_application' => 0,
                        'payment_plan' => 'consulation fee',
                        'payment_comment' => 'This fee for next exam # '.$fee['exam_no'].' '.$this->getOrdinal($fee['class']).' '.$fee['course_type'],
                        'system_comment' => 'Council auto-generated fee',
                    
                        'add_by' => $this->session->userdata('name')
                    ];
                    
                    // print_r($insert);
                    // exit();
        
                    $already = $this->db->get_where('payments', [
                        'student_id' => $student_id,
                        'exam_sequence_id' => $fee['next_exam_sequence_id'],
                        'exam_class' => $fee['class'],
                        'council_sequence_id' => $fee['council_sequence_id']
                    ])->row_array();
        
                    if (!$already) {
                        $this->db->insert('payments', $insert);
                    }
                }
            }
        }
    
        $this->session->set_userdata('message', 'Fees created successfully for all valid students.');
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    function getOrdinal($number)
    {
        $suffixes = ['th','st','nd','rd','th','th','th','th','th','th'];
    
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }
    
        return $number . $suffixes[$number % 10];
    }
    
    public function save_council_exam($exam_id,$status)
    {
        $data = array( 'status' => $status );
        
        $this->db->where('id',$exam_id);
        $this->db->update('exam_sequence',$data);
    
        redirect($_SERVER['HTTP_REFERER']);
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
    
    public function load_student_fee_status()
    {
        $student_id = $this->input->post('student_id');
        $course_id = $this->input->post('course_id');
        $exam_no = $this->input->post('exam_no');
        $class = $this->input->post('class');
        $sequence_id = $this->input->post('sequence_id');
        $exam_sequence = $this->input->post('exam_sequence');
    
        $student = $this->db
            ->select('students.*, classes.name as class_name')
            ->from('students')
            ->join('classes', 'classes.class_id = students.class_id', 'left')
            ->where('students.student_id', $student_id)
            ->get()
            ->row_array();
    
        $sequence = $this->db->get_where('council_sequence', [
            'council_sequence_id' => $sequence_id
        ])->row_array();
    
        $current_exam_sequence = $this->db->get_where('exam_sequence', [
            'id' => $exam_sequence
        ])->row_array();
    
        $course = $this->db->get_where('courses', [
            'course_id' => $course_id
        ])->row_array();
    
        $result_rules = $this->db->get_where('council_result_rules', [
            'course_id' => $course_id
        ])->row_array();
    
        $roll_number_and_result = $this->db->get_where('punjab_council_roll_number', [
            'cnic' => $student['cnic'],
            'council_exam_no' => $exam_no,
            'class' => $class,
            'course_id' => $course_id
        ])->row_array();
    
        $student_fee_items = [];
        $student_has_error = false;
        $bulk_fee_has_errors = false;
        $is_year = $course['course_type'] == "Annual" ? "Year" : $course['course_type'];
    
        ob_start();
    
        // IMPORTANT:
        // Yahan apna old Next Fee Status wala pura PHP code paste kar do.
        // Sirf <td> aur </td> paste nahi karna.
        // Andar wala code same paste karna:
        //
        // if (!empty($roll_number_and_result['result_remarks'])) {
        //     ...
        // } else {
        //     echo "-";
        // }
    
        include APPPATH.'views/councils/partials/student_fee_status_logic.php';
    
        $html = ob_get_clean();
    
        echo json_encode([
            'html' => $html,
            'fee_items' => $student_fee_items,
            'has_error' => $student_has_error ? 1 : 0
        ]);
    }
}
