<?php

/**
 * Created by PhpStorm.
 * User: abdul
 * Date: 1/1/2021
 * Time: 11:56 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class AdvertisementDevices extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->email = $this->db->get_where('sms_gateway', array('id'=>1))->row()->email;
        $this->password = $this->db->get_where('sms_gateway', array('id'=>1))->row()->password;
    }

    public function index()
    {
        $data['devices_list'] = $this->db->get('advertisement_devices')->result_array();
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('advertisement/index', $data);
        $this->load->view('inc/footer');
    }
    public function add_device(){
       // $date = new DateTime();
       // print_r($date);
        $data = $this->input->post();
        $this->db->set('device_id', $this->input->post('device_id'));
        $this->db->set('device_no', $this->input->post('device_no'));
        $this->db->set('mobile_no', $this->input->post('mobile_no'));
        $this->db->set('created_at', date('Y-m-d H:i:s'));
        $this->db->set('updated_at', date('Y-m-d H:i:s'));
        $this->db->insert('advertisement_devices');

        $data['devices_list'] = $this->db->get('advertisement_devices')->result_array();
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('advertisement/index', $data);
        $this->load->view('inc/footer');
    }
    public function edit_device($id){

        $data['edit_device']  = $this->db->get_where('advertisement_devices',array('id'=>$id))->row();

        $data['devices_list'] = $this->db->get('advertisement_devices')->result_array();
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('advertisement/index',$data);
        $this->load->view('inc/footer');
    }

    public function update_device(){
        $data = $this->input->post();
        $this->db->set('device_id', $this->input->post('device_id'));
        $this->db->set('device_no', $this->input->post('device_no'));
        $this->db->set('mobile_no', $this->input->post('mobile_no'));
        $this->db->set('updated_at', date('Y-m-d H:i:s'));
        $this->db->where('id',$this->input->post('id'));
        $this->db->update('advertisement_devices');

        $data['devices_list'] = $this->db->get('advertisement_devices')->result_array();
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('advertisement/index',$data);
        $this->load->view('inc/footer');
    }





}