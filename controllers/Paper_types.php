<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Paper_types extends CI_Controller {

    public function add_paper_type()
    {
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('paper_types/add_paper_type');
        $this->load->view('inc/footer');
    }
    
    public function insert()
    {
        $name = $this->input->post('name');
        
        $this->db->set('name',$name);
        $this->db->insert('paper_types');
        
        $this->session->set_flashdata('message', 'Paper Type added successfully.');
        redirect('paper_types/add_paper_type');
    }
    
    public function all_paper_types()
    {
        $data['paper_types'] = $this->db->get('paper_types')->result_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('paper_types/all_paper_types',$data);
        $this->load->view('inc/footer');
    }
    
    public function edit_paper_type($paper_type_id)
    {
        $data['paper_type'] = $this->db->get_where('paper_types',array('paper_type_id'=>$paper_type_id))->result_array();
        
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('paper_types/edit_paper_type',$data);
        $this->load->view('inc/footer');
    }
    
    public function update($paper_type_id)
    {
        $name = $this->input->post('name');
        
        $this->db->set('name',$name);
        $this->db->where('paper_type_id',$paper_type_id);
        $this->db->update('paper_types');
        
        $this->session->set_flashdata('message', 'Paper Type updated successfully.');
        redirect('paper_types/edit_paper_type/'.$paper_type_id);
    }
    
    public function delete($paper_type_id)
    {
        $this->db->where('paper_type_id',$paper_type_id);
        $this->db->delete('paper_types');
        
        $this->session->set_flashdata('message', 'Paper type deleted successfully.');
        redirect('paper_types/all_paper_types');
    }
}
