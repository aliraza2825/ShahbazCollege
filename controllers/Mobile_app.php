<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Mobile_app extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('upload');
    }

    public function manage_campuses()
    {
        $data['campuses'] = $this->db->get('campuses')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_app/manage_campuses',$data);
        $this->load->view('inc/footer');
    }

    public function edit_campus($campus_id)
    {
        $data['campus'] = $this->db->get_where('campuses',array('campus_id'=>$campus_id))->result_array();
        $data['designations'] = $this->db->get('designations')->result_array();

        $this->db->select('users_phones.*');
        $this->db->from('users');
        $this->db->join('designations','designations.designation_id=users.designation_id','inner');
        $this->db->join('users_phones','users_phones.user_id=users.user_id','inner');
        $this->db->where(array('users.campus_id'=>$campus_id,'designations.designation_name'=>'Receptionist'));
        $data['receptionist'] = $this->db->get()->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_app/edit_campus',$data);
        $this->load->view('inc/footer');
    }

    public function update_campus($campus_id)
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
        if (!$this->upload->do_upload('campus_image')) 
        {
            $data = array('msg' => $this->upload->display_errors());
            $campus_image = $this->input->post('old_campus_image');
            $this->db->set('campus_image',$campus_image);
            $this->db->set('mobile_status',$this->input->post('mobile_status'));
            $this->db->set('google_map_link',$this->input->post('google_map_link'));
            $this->db->set('facebook',$this->input->post('facebook'));
            $this->db->set('twitter',$this->input->post('twitter'));
            $this->db->set('whatsapp','https://wa.me/'.$this->input->post('whatsapp'));
            $this->db->set('content',$this->input->post('content'));
            $designations = $this->input->post('designation_id');
		
            if($designations!='')
            {
                $designations =  implode(",", $designations);
            }
            $this->db->set('designation_ids',$designations);
            $this->db->where('campus_id',$campus_id);
            $this->db->update('campuses');

        } 
        else 
        { //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $campus_image = $data['upload_data']['file_name'];

                $this->db->set('campus_image',$campus_image);
                $this->db->set('mobile_status',$this->input->post('mobile_status'));
                $this->db->set('google_map_link',$this->input->post('google_map_link'));
                $this->db->set('facebook',$this->input->post('facebook'));
                $this->db->set('twitter',$this->input->post('twitter'));
                $this->db->set('whatsapp','https://wa.me/'.$this->input->post('whatsapp'));
                $this->db->set('content',$this->input->post('content'));
                $designations = $this->input->post('designation_id');
		
                if($designations!='')
                {
                    $designations =  implode(",", $designations);
                }
                $this->db->set('designation_ids',$designations);
                $this->db->where('campus_id',$campus_id);
                $this->db->update('campuses');

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL,"https://www.shahbazcollegeofpharmacy.edu.pk/s3/upload_campus_image.php");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS,"campus_id=".$campus_id);

                // Receive server response ...
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $server_output = curl_exec($ch);

                curl_close($ch);
            }
        }

        $this->session->set_flashdata('message','Campus Updated Successfully.');
        redirect('mobile_app/edit_campus/'.$campus_id);
        
    }

    public function manage_courses()
    {
        $data['courses'] = $this->db->get('courses')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_app/manage_courses',$data);
        $this->load->view('inc/footer');
    }

    public function edit_course($course_id)
    {
        $data['course'] = $this->db->get_where('courses',array('course_id'=>$course_id))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_app/edit_course',$data);
        $this->load->view('inc/footer');
    }

    public function update_course($course_id)
    {
        $content = $this->input->post('content');
        $mobile_status = $this->input->post('mobile_status');

        $this->db->set('content',$content);
        $this->db->set('mobile_status',$mobile_status);
        $this->db->where('course_id',$course_id);
        $this->db->update('courses');

        $this->session->set_flashdata('message','Course Updated Successfully.');
        redirect('mobile_app/edit_course/'.$course_id);
    }

    public function manage_images()
    {
        $data['advertisements'] = $this->db->get('mobile_advertisement')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_app/manage_images', $data);
        $this->load->view('inc/footer');
    }

    public function insert_image()
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
        if($this->input->post('expire')==1)
        {
            $this->db->set('expire_date',$this->input->post('expire_date'));
        }

        $this->db->insert('mobile_advertisement');
        $insert_id = $this->db->insert_id();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://www.shahbazcollegeofpharmacy.edu.pk/s3/upload_mobile_advertisement_image.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,"id=".$insert_id);

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);

        $this->session->set_flashdata('message','Image Updated Successfully.');
        redirect('mobile_app/manage_images');
    }

    public function manage_news_updates()
    {
        $data['news_updates'] = $this->db->get('news_updates')->result_array();
        $data['courses'] = $this->db->get('courses')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_app/manage_news_updates',$data);
        $this->load->view('inc/footer');
    }

    public function edit_news_updates($news_id)
    {
        $data['news_update'] = $this->db->get_where('news_updates',array('news_id'=>$news_id))->result_array();
        $data['courses'] = $this->db->get('courses')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_app/edit_news_updates',$data);
        $this->load->view('inc/footer');
    }

    public function insert_news_updates()
    {
        $course_ids = implode(',',$this->input->post('course_ids'));
        $news = $this->input->post('news');

        $this->db->set('course_ids',$course_ids);
        $this->db->set('news',$news);
        $this->db->insert('news_updates');

        $this->session->set_flashdata('message', 'News Added Successfully!');
        redirect('mobile_app/manage_news_updates');
    }

    public function update_news_updates($news_id)
    {
        $course_ids = implode(',',$this->input->post('course_ids'));
        $news = $this->input->post('news');

        $this->db->set('course_ids',$course_ids);
        $this->db->set('news',$news);
        $this->db->where('news_id',$news_id);
        $this->db->update('news_updates');

        $this->session->set_flashdata('message', 'News Updated Successfully!');
        redirect('mobile_app/edit_news_updates/'.$news_id);
    }

    public function delete_news_updates($news_id)
    {
        $this->db->where('news_id',$news_id);
        $this->db->delete('news_updates');

        $this->session->set_flashdata('message', 'News Deleted Successfully!');
        redirect('mobile_app/manage_news_updates');
    }

    public function manage_complaint_types()
    {
        $data['complaints'] = $this->db->get('complaint_types')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_app/manage_complaint_types',$data);
        $this->load->view('inc/footer');
    }

    public function insert_complaint_type()
    {
        $complaint_type = $this->input->post('complaint_type');
        $status = $this->input->post('status');

        $this->db->set('complaint_type',$complaint_type);
        $this->db->set('status',$status);
        $this->db->insert('complaint_types');

        $this->session->set_flashdata('message', 'Complaint Type Added Successfully');
        redirect('mobile_app/manage_complaint_types');
    }

    public function edit_complaint_type($complaint_type_id)
    {
        $data['complaint'] = $this->db->get_where('complaint_types',array('complaint_type_id'=>$complaint_type_id))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_app/edit_complaint_type',$data);
        $this->load->view('inc/footer');
    }

    public function update_complaint_type($complaint_type_id)
    {
        $complaint_type = $this->input->post('complaint_type');
        $status = $this->input->post('status');

        $this->db->set('complaint_type',$complaint_type);
        $this->db->set('status',$status);
        $this->db->where('complaint_type_id',$complaint_type_id);
        $this->db->update('complaint_types');

        $this->session->set_flashdata('message', 'Complaint Type Updated Successfully');
        redirect('mobile_app/manage_complaint_types');
    }

    public function delete_coplaint_type($complaint_type_id)
    {
        $this->db->where('complaint_type_id',$complaint_type_id);
        $this->db->delete('complaint_types');

        $this->session->set_flashdata('message', 'Complaint Type Deleted Successfully');
        redirect('mobile_app/manage_complaint_types');
    }

    public function required_career()
    {
        $data['required_careers'] = $this->db->get('required_career')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_app/required_career',$data);
        $this->load->view('inc/footer');
    }

    public function insert_required_career()
    {
        $required_career = $this->input->post('required_career');
        $status = $this->input->post('status');

        $this->db->set('required_career',$required_career);
        $this->db->set('status',$status);
        $this->db->insert('required_career');

        $this->session->set_flashdata('message', 'Career Added Successfully');
        redirect('mobile_app/required_career');
    }

    public function edit_required_career($required_career_id)
    {
        $data['required_career'] = $this->db->get_where('required_career',array('required_career_id'=>$required_career_id))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('mobile_app/edit_required_career',$data);
        $this->load->view('inc/footer');
    }

    public function update_required_career($required_career_id)
    {
        $required_career = $this->input->post('required_career');
        $status = $this->input->post('status');

        $this->db->set('required_career',$required_career);
        $this->db->set('status',$status);
        $this->db->where('required_career_id',$required_career_id);
        $this->db->update('required_career');

        $this->session->set_flashdata('message', 'Complaint Type Updated Successfully');
        redirect('mobile_app/required_career');
    }
}
