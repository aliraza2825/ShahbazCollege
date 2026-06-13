<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Campuses extends CI_Controller {

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
    public function index()
    {
        //$this->load->view('welcome_message');
    }

    public function add_campus()
    {
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('campuses/add_campus');
        $this->load->view('inc/footer');
    }

    public function edit_campus($campus_id)
    {
        $data['campuses'] = $this->db->get_where('campuses', array('campus_id'=>$campus_id))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('campuses/edit_campus', $data);
        $this->load->view('inc/footer');
    }

    public function insert()
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
        if (!$this->upload->do_upload('logo')) {
            $data = array('msg' => $this->upload->display_errors());
            $logo = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $logo = $data['upload_data']['file_name'];
            }
        }

        //if not successful, set the error message
        if (!$this->upload->do_upload('stamp')) {
            $data = array('msg' => $this->upload->display_errors());
            $stamp = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $stamp = $data['upload_data']['file_name'];
            }
        }


        //if not successful, set the error message
        if (!$this->upload->do_upload('head_stamp')) {
            $data = array('msg' => $this->upload->display_errors());
            $head_stamp = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $head_stamp = $data['upload_data']['file_name'];
            }
        }

        $campus_code = $this->input->post('campus_code');
        $campus_name = $this->input->post('campus_name');
        $website = $this->input->post('website');
        $address = $this->input->post('address');
        $sms = $this->input->post('sms');
        $phone = $this->input->post('phone');
        $phone1 = $this->input->post('phone1');
        $phone2 = $this->input->post('phone2');
        $phone3 = $this->input->post('phone3');
        $phone4 = $this->input->post('phone4');
        $phone5 = $this->input->post('phone5');
        $phone6 = $this->input->post('phone6');
        $phone7 = $this->input->post('phone7');
        $facebook_api = $this->input->post('facebook_api');
        $bank_name = $this->input->post('bank_name');
        $account_no = $this->input->post('account_no');
        $note = $this->input->post('note');
        $roll_no_code = $this->input->post('roll_no_code');

        $this->db->set('logo', $logo);
        $this->db->set('stamp', $stamp);
        $this->db->set('head_stamp', $head_stamp);
        $this->db->set('campus_name', $campus_name);
        $this->db->set('roll_no_code', $roll_no_code);
        $this->db->set('website', $website);
        $this->db->set('address', $address);
        $this->db->set('sms', $sms);
        $this->db->set('phone', $phone);
        $this->db->set('phone1', $phone1);
        $this->db->set('phone2', $phone2);
        $this->db->set('phone3', $phone3);
        $this->db->set('phone4', $phone4);
        $this->db->set('phone5', $phone5);
        $this->db->set('phone6', $phone6);
        $this->db->set('phone7', $phone7);
        $this->db->set('facebook_api', $facebook_api);
        $this->db->set('bank_name', $bank_name);
        $this->db->set('account_no', $account_no);
        $this->db->set('note', $note);
        $this->db->set('for_mobile_application', $this->input->post('for_mobile_application'));
        $this->db->insert('campuses');

        $this->session->set_flashdata('message', 'Campus has been added.');
        redirect('campuses/add_campus');
    }

    public function update($campus_id)
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
        if (!$this->upload->do_upload('logo')) {
            $data = array('msg' => $this->upload->display_errors());
            $logo = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $logo = $data['upload_data']['file_name'];
            }
        }

        if($logo=='')
        {
            $logo = $this->input->post('old_logo');
        }

        if (!$this->upload->do_upload('stamp')) {
            $data = array('msg' => $this->upload->display_errors());
            $stamp = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $stamp = $data['upload_data']['file_name'];
            }
        }

        if($stamp=='')
        {
            $stamp = $this->input->post('old_stamp');
        }


        if (!$this->upload->do_upload('head_stamp')) {
            $data = array('msg' => $this->upload->display_errors());
            $head_stamp = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $head_stamp = $data['upload_data']['file_name'];
            }
        }

        if($head_stamp=='')
        {
            $head_stamp = $this->input->post('old_head_stamp');
        }

        $campus_code = $this->input->post('campus_code');
        $campus_name = $this->input->post('campus_name');
        $website = $this->input->post('website');
        $address = $this->input->post('address');
        $sms = $this->input->post('sms');
        $phone = $this->input->post('phone');
        $phone1 = $this->input->post('phone1');
        $phone2 = $this->input->post('phone2');
        $phone3 = $this->input->post('phone3');
        $phone4 = $this->input->post('phone4');
        $phone5 = $this->input->post('phone5');
        $phone6 = $this->input->post('phone6');
        $phone7 = $this->input->post('phone7');
        $facebook_api = $this->input->post('facebook_api');
        $bank_name = $this->input->post('bank_name');
        $account_no = $this->input->post('account_no');
        $note = $this->input->post('note');
        $email = $this->input->post('email');
        $roll_no_code = $this->input->post('roll_no_code');

        $this->db->set('logo', $logo);
        $this->db->set('stamp', $stamp);
        $this->db->set('head_stamp', $head_stamp);
        $this->db->set('campus_code', $campus_code);
        $this->db->set('campus_name', $campus_name);
        $this->db->set('roll_no_code', $roll_no_code);
        $this->db->set('website', $website);
        $this->db->set('address', $address);
        $this->db->set('sms', $sms);
        $this->db->set('phone', $phone);
        $this->db->set('phone1', $phone1);
        $this->db->set('phone2', $phone2);
        $this->db->set('phone3', $phone3);
        $this->db->set('phone4', $phone4);
        $this->db->set('phone5', $phone5);
        $this->db->set('phone6', $phone6);
        $this->db->set('phone7', $phone7);
        $this->db->set('facebook_api', $facebook_api);
        $this->db->set('bank_name', $bank_name);
        $this->db->set('account_no', $account_no);
        $this->db->set('note', $note);
        $this->db->set('email', $email);
        $this->db->where('campus_id', $campus_id);
        $this->db->set('for_mobile_application', $this->input->post('for_mobile_application'));
        $this->db->update('campuses');

        $this->session->set_flashdata('message', 'Campus has been updated.');
        redirect('campuses/edit_campus/'.$campus_id);
    }

    public function all_campuses()
    {
        $data['campuses'] = $this->db->get_where('campuses',array('status'=>1))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('campuses/all_campuses', $data);
        $this->load->view('inc/footer');
    }

    public function delete($campus_id)
    {
        $this->db->set('status',0);
        $this->db->where('campus_id', $campus_id);
        $this->db->update('campuses');

        $this->session->set_flashdata('message', 'Campus has been deleted.');
        redirect('campuses/all_campuses');
    }

    public function manage_campus_profit()
    {
        $data['campuses'] = $this->db->get('campuses')->result_array();
        $data['users'] = $this->db->get_where('users', array('status'=>1))->result_array();

        $this->db->select('campus_partners.*, campuses.campus_name');
        $this->db->from('campus_partners');
        $this->db->join('campuses', 'campus_partners.campus_id=campuses.campus_id', 'inner');

        $data['campus_partners'] = $this->db->get()->result_array();

        $data['expense_categories'] =$this->db->get('expense_category')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('campuses/manage_campus', $data);
        $this->load->view('inc/footer');
    }

    public function edit_manage_campus_profit($campus_partner_id)
    {
        $data['campuses'] = $this->db->get('campuses')->result_array();
        $data['users'] = $this->db->get_where('users', array('status'=>1))->result_array();

        $data['current_campuses'] = $this->db->get_where('campus_partners', array('campus_partner_id'=>$campus_partner_id))->result_array();

        $this->db->select('campus_partners.*, campuses.campus_name');
        $this->db->from('campus_partners');
        $this->db->join('campuses', 'campus_partners.campus_id=campuses.campus_id', 'inner');

        $data['campus_partners'] = $this->db->get()->result_array();

        $data['expense_categories'] =$this->db->get('expense_category')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('campuses/edit_manage_campus', $data);
        $this->load->view('inc/footer');
    }

    public function insert_partners()
    {
        $campus_id = $this->input->post('campus_id');
        $campus_shared = $this->input->post('campus_share_ids');
        $no_of_seats = $this->input->post('no_of_seats');
        $special_expense_ids = $this->input->post('special_expense_ids');

        $check_campus_record = $this->db->get_where('campus_partners', array('campus_id'=>$campus_id))->result_array();
        if(count($check_campus_record)>0)
        {
            $this->session->set_flashdata('error', 'Error! Record Already exists');
            redirect('campuses/manage_campus_profit');
        }

        $count = (count($this->input->post())-1)/2;
        $partner = array();
        for($i=1; $i<=$count; $i++)
        {
            array_push($partner, $this->input->post('user_id_'.$i));
            array_push($partner, $this->input->post('percentage_'.$i));
        }
        $partners = json_encode($partner);

        $this->db->set('campus_id', $campus_id);
        $this->db->set('partners', $partners);
        $this->db->set('campus_share_ids', json_encode($campus_shared));
        $this->db->set('no_of_seats', json_encode($no_of_seats));
        $this->db->set('special_expense_ids',json_encode($special_expense_ids));
        $this->db->insert('campus_partners');

        $this->session->set_flashdata('message', 'Added successfully.');
        redirect('campuses/manage_campus_profit');
    }

    public function update_partners($campus_partner_id)
    {
        $campus_id = $this->input->post('campus_id');
        $campus_shared = $this->input->post('campus_share_ids');
        $no_of_seats = $this->input->post('no_of_seats');
        $special_expense_ids = $this->input->post('special_expense_ids');

        //echo '<pre>';
        //print_r($this->input->post());
        //echo '</pre>';
        $count = (count($this->input->post())-4)/2;
        //echo $count; exit;
        $partner = array();
        for($i=1; $i<=$count; $i++)
        {
            array_push($partner, $this->input->post('user_id_'.$i));
            array_push($partner, $this->input->post('percentage_'.$i));
        }
        $partners = json_encode($partner);

        $this->db->set('campus_id', $campus_id);
        $this->db->set('partners', $partners);
        $this->db->set('campus_share_ids', json_encode($campus_shared));
        $this->db->set('no_of_seats', json_encode($no_of_seats));
        $this->db->set('special_expense_ids',json_encode($special_expense_ids));
        $this->db->where('campus_partner_id', $campus_partner_id);
        $this->db->update('campus_partners');

        $this->session->set_flashdata('message', 'Updated successfully.');
        redirect('campuses/edit_manage_campus_profit/'.$campus_partner_id);
    }

    public function delete_partners($campus_partner_id)
    {
        $this->db->where('campus_partner_id', $campus_partner_id);
        $this->db->delete('campus_partners');

        $this->session->set_flashdata('message', 'Partners has been deleted.');
        redirect('campuses/manage_campus_profit');
    }

    public function upload_campus_documents($campus_id)
    {
        $data['campuses'] = $this->db->get_where('campuses', array('campus_id'=>$campus_id))->result_array();
        $data['documents'] = $this->db->get_where('campus_documents', array('campus_id'=>$campus_id))->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('campuses/upload_documents', $data);
        $this->load->view('inc/footer');
    }

    public function delete_documents($campus_id, $photo_id)
    {
        $this->db->where('campus_document_id', $photo_id);
        $this->db->delete('campus_documents');

        $this->session->set_flashdata('message', 'Document deleted successfully');
        redirect('campuses/upload_campus_documents/'.$campus_id);
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
        if (!$this->upload->do_upload('document')) {
            $data = array('msg' => $this->upload->display_errors());
            $document = '';

        }
        else
        {
            //else, set the success message
            $data['upload_data'] = $this->upload->data();
            if($data['upload_data']['file_name']){
                $document = $data['upload_data']['file_name'];
            }
        }

        $type = $this->input->post('type');

        $this->db->set('type', $type);
        $this->db->set('document', $document);
        $this->db->set('campus_id', $id);
        $this->db->insert('campus_documents');

        $this->session->set_flashdata('message', 'Document Uploaded Successfully.');
        redirect('campuses/upload_campus_documents/'.$id);
    }

    public function print_challans($campus_id)
    {
        $data['campuses']=$this->db->get_where('campuses', array('campus_id'=>$campus_id))->result_array();

        $this->load->view('campuses/challan', $data);
    }
}
