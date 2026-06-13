<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Mobile_application extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('upload');
        require_once("vendor/autoload.php");
    }

    public function assign_teachers()
    {
        $data['courses'] = $this->db->get_where('courses','status = 1')->result_array();
        $data['users'] = $this->db->join('designations','designations.designation_id = users.designation_id')
            ->join('departments','departments.department_id = users.department_id')->get_where('users','users.status = 1 and departments.department_id = 13')->result_array();
        $data['teachers'] = $this->db->join('courses','courses.course_id = mobile_teachers_chat.course_id')
            ->join('course_subjects','course_subjects.course_subject_id = mobile_teachers_chat.subject_id')
            ->join('users','users.user_id = mobile_teachers_chat.teacher_id')
            ->get('mobile_teachers_chat')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_application/add_teachers', $data);
        $this->load->view('inc/footer');
    }

    public function assign_support()
    {

        $data['designations'] = $this->db->get('designations')->result_array();
        $data['support_staff'] = $this->db->get('support_staff')->result_array();


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_application/assign_support', $data);
        $this->load->view('inc/footer');
    }

    public function insert_teacher()
    {

        $this->db->set('course_id',$this->input->post('course_id'));
        $this->db->set('subject_id',$this->input->post('subject_id'));
        $this->db->set('teacher_id',$this->input->post('teacher_id'));

        $this->db->insert('mobile_teachers_chat');

        $this->session->set_flashdata('message','Teacher Add Successfully.');
        redirect('mobile_application/assign_teachers');
    }

    public function insert_chatt_support()
    {
        $this->db->set('designation_id',$this->input->post('course_id'));
        $this->db->where('id',"1");
        $this->db->update('support_staff');

        $this->session->set_flashdata('message','Updated Successfully.');
        redirect('mobile_application/assign_support');
    }

    public function delete_teacher($id)
    {

        $this->db->where("id",$id)->delete('mobile_teachers_chat');

        $this->session->set_flashdata('message','Teacher Deleted Successfully.');
        redirect('mobile_application/assign_teachers');
    }

    public function add_mobile_advertisement()
    {
        $data['advertisements'] = $this->db->get('mobile_advertisement')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_application/mobile_advertisement', $data);
        $this->load->view('inc/footer');
    }

    public function insert_advertisement()
    {
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
        if (!$this->upload->do_upload('picture')) {
            $data = array('msg' => $this->upload->display_errors());
            $picture = '';
        }
        else {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if ($data['upload_data']['file_name']) {
                $picture = $data['upload_data']['file_name'];
            }
        }

        $this->db->set('title',$this->input->post('title'));
        $this->db->set('description',$this->input->post('description'));
        $this->db->set('type',$this->input->post('course_id'));
        $this->db->set('file',$picture);

        $this->db->insert('mobile_advertisement');

        $this->session->set_flashdata('message','Advertisement added Successfully.');
        redirect('mobile_application/add_mobile_advertisement');
    }

    public function delete_advertisement($id)
    {
        $this->db->where("id",$id)->delete('mobile_advertisement');
        $this->session->set_flashdata('message','Advertisement Deleted Successfully.');
        redirect('mobile_application/add_mobile_advertisement');
    }

    public function sale_products()
    {
        $data['sale_products'] = $this->db->get('sale_products')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_application/sale_products', $data);
        $this->load->view('inc/footer');
    }

    public function insert_product()
    {
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
        if (!$this->upload->do_upload('picture')) {
            $data = array('msg' => $this->upload->display_errors());
            $picture = '';
        }
        else {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if ($data['upload_data']['file_name']) {
                $picture = $data['upload_data']['file_name'];
            }
        }

        $this->db->set('name',$this->input->post('name'));
        $this->db->set('description',$this->input->post('description'));
        $this->db->set('price',$this->input->post('price'));
        $this->db->set('image',$picture);

        $this->db->insert('sale_products');

        $this->session->set_flashdata('message','Sale Product added Successfully.');
        redirect('mobile_application/sale_products');
    }

    public function delete_product($id)
    {
        $this->db->where("id",$id)->delete('sale_products');
        $this->session->set_flashdata('message','Product Deleted Successfully.');
        redirect('mobile_application/sale_products');
    }

    public function online_orders()
    {
        $data['mobile_app_orders'] = $this->db->get('mobile_app_orders')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_application/sale_products', $data);
        $this->load->view('inc/footer');
    }

    public function add_courses_app()
    {
        $data['courses'] = $this->db->get_where('courses','status = 1')->result_array();
        $data['campuses'] = $this->db->get_where('campuses','status = 1')->result_array();
        $data['courses_sessions'] = $this->db->
        join('courses','courses.course_id = courses_sessions_mobile_app.course_id')->
        join('campuses','campuses.campus_id = courses_sessions_mobile_app.campus_id')
            ->join('classes','classes.class_id = courses_sessions_mobile_app.class_id')
            ->get('courses_sessions_mobile_app')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_application/add_mobile_courses', $data);
        $this->load->view('inc/footer');
    }

    public function insert_course_session()
    {
        $this->db->set('course_id',$this->input->post('course_id'));
        $this->db->set('campus_id',$this->input->post('campus_id'));
        $this->db->set('class_id',$this->input->post('class_id'));
        $this->db->insert('courses_sessions_mobile_app');

        $this->session->set_flashdata('message','Add Successfully.');
        redirect('mobile_application/add_courses_app');
    }

    public function add_invite_rule()
    {
        $data['invite_rules'] = $this->db->get_where('invite_rules','id = 1')->row();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_application/add_invite_rules', $data);
        $this->load->view('inc/footer');
    }

    public function insert_points_rules()
    {
        $this->db->set('install_points',$this->input->post('install_points'));
        $this->db->set('admission_points',$this->input->post('admission_points'));
        $this->db->set('points_to_rupees',$this->input->post('points_to_rupees'));
        $this->db->where('id',"1");
        $this->db->update('invite_rules');

        $this->session->set_flashdata('message','Add Successfully.');
        redirect('mobile_application/add_courses_app');
    }

    public function advertising_materials()
    {
        $data['advertisements'] = $this->db->join("users","users.user_id = advertising_materials.user_id")->get('advertising_materials')->result_array();
        $data['users'] = $this->db->join('designations','designations.designation_id = users.designation_id')
            ->join('departments','departments.department_id = users.department_id')->get_where('users','users.status = 1')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_application/advertisement_material', $data);
        $this->load->view('inc/footer');
    }

    public function insert_advertisement_material()
    {

        if (!is_dir(getcwd().'/materials')) {
            mkdir(getcwd().'/materials', 0777);
        }
        $this->load->helper('form');
        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'materials/';
        // set the filter image types
        $config['allowed_types'] = '*';
        //load the upload library
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->set_allowed_types('*');
        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('picture')) {
            $data = array('msg' => $this->upload->display_errors());
            $picture = '';
            $mime = "";
        }
        else {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if ($data['upload_data']['file_name']) {
                $picture = $data['upload_data']['file_name'];
                $mime = $data['upload_data']['file_type'];
            }
        }

        $this->db->set('user_id',$this->input->post('user_id'));
        $this->db->set('text',$this->input->post('description'));
        $this->db->set('type',$mime);
        $this->db->set('file',$picture);

        $this->db->insert('advertising_materials');

        $this->session->set_flashdata('message','Advertisement added Successfully.');
        redirect('mobile_application/advertising_materials');
    }

    public function delete_advertisement_material($id)
    {
        $this->db->where("id",$id)->delete('advertising_materials');
        $this->session->set_flashdata('message','Advertisement Deleted Successfully.');
        redirect('mobile_application/advertising_materials');
    }

    public function delete_course($id)
    {
        $this->db->where("id",$id)->delete('courses_sessions_mobile_app');
        $this->session->set_flashdata('message','Advertisement Deleted Successfully.');
        redirect('mobile_application/add_courses_app');
    }

    public function add_youtube_video()
    {
        $data['youtube_link'] = $this->db->get_where('youtube_links','id = 1')->row();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_application/assign_youtube_video', $data);
        $this->load->view('inc/footer');
    }

    public function insert_youtube_video()
    {
        $this->db->set('video_id',$this->input->post('id'));
        $this->db->where('id',"1");
        $this->db->update('youtube_links');

        $this->session->set_flashdata('message','Add Successfully.');
        redirect('mobile_application/add_youtube_video');
    }

    public function add_study_rule_video()
    {
        $data['courses'] = $this->db->get_where('courses','status = "1"')->result_array();
        $data['rules'] = $this->db->select("study_rules_app.*,courses.course_name")->join("courses","courses.course_id = study_rules_app.course_id")->get('study_rules_app')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_application/study_rules_mobile', $data);
        $this->load->view('inc/footer');
    }

    public function insert_study_rule()
    {
        $this->db->set('course_id',$this->input->post('course_id'));
        $this->db->set('months',$this->input->post('months'));
        $this->db->set('test_after_lectures',$this->input->post('test_after_lectures'));
        $this->db->set('created_by',$this->session->userdata('name'));
        $this->db->insert('study_rules_app');

        $this->session->set_flashdata('message','Add Successfully.');
        redirect('mobile_application/add_study_rule_video');
    }

    public function change_study_status($id,$status)
    {
        $this->db->set('status',$status);
        $this->db->set('created_by',$this->session->userdata('name'));
        $this->db->where('id',$id);
        $this->db->update('study_rules_app');

        $this->session->set_flashdata('message','Add Successfully.');
        redirect('mobile_application/add_study_rule_video');
    }

    public function add_how_to_use($module)
    {
        $data['how_to_use'] = $this->db->get_where('how_to_use','module = "'.$module.'"')->result_array();
        $data['module'] = $module;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_application/how_to_use_list', $data);
        $this->load->view('inc/footer');
    }

    public function insert_how_to_use()
    {

        if (!is_dir(getcwd().'/how_to_use')) {
            mkdir(getcwd().'/how_to_use', 0777);
        }
        $this->load->helper('form');
        //Configure
        //set the path where the files uploaded will be copied. NOTE if using linux, set the folder to permission 777
        $config['upload_path'] = 'how_to_use/';
        // set the filter image types
        $config['allowed_types'] = '*';
        //load the upload library
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->set_allowed_types('*');
        $data['upload_data'] = '';

        //if not successful, set the error message
        if (!$this->upload->do_upload('picture')) {
            $data = array('msg' => $this->upload->display_errors());
            $picture = '';
            $mime = "";
        }
        else {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if ($data['upload_data']['file_name']) {
                $picture = $data['upload_data']['file_name'];
                $mime = $data['upload_data']['file_type'];
            }
        }

        $this->db->set('title',$this->input->post('title'));
        $this->db->set('file_type',$mime);
        $this->db->set('file',$picture);
        $this->db->set('module',$this->input->post('module'));
        $this->db->set('created_by',$this->session->userdata('name'));
        $this->db->insert('how_to_use');

        $this->session->set_flashdata('message','Advertisement added Successfully.');
        redirect('mobile_application/add_how_to_use/'.$this->input->post('module'));
    }

    public function delete_how_to_use($id,$module)
    {
        $fil = $this->db->get_where("how_to_use","id = '$id'")->row();
        $path_to_file = 'how_to_use/'.$fil->file;
        $this->db->where("id",$id)->delete('how_to_use');

        if(unlink($path_to_file)) {
            $this->session->set_flashdata('message','Advertisement Deleted Successfully.');
            redirect('mobile_application/add_how_to_use/'.$module);
        }
        else {
            echo 'errors occured';
        }

    }

    public function course_details()
    {
        $data['courses'] = $this->db->get('courses')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_application/course_details',$data);
        $this->load->view('inc/footer');
    }

    public function insert_course_details()
    {
        $content = $this->input->post('content');
        $course_id = $this->input->post('course_id');

        $this->db->set('content',$content);
        $this->db->where('course_id',$course_id);
        $this->db->update('courses');

        $this->session->set_flashdata('message','Course Content Updated Successfully.');
        redirect('mobile_application/course_details');
    }

    public function getCourseContent()
    {
        $course_id = $this->input->post('course_id');
        $course_details = $this->db->get_where('courses',array('course_id'=>$course_id))->result_array();

        echo $course_details[0]['content'];
    }
}
