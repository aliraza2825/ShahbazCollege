<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payroll_statutory_rules extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('account');
    }

    public function index()
    {
        $data['rules'] = $this->db
            ->order_by('id', 'DESC')
            ->get('payroll_statutory_rules')
            ->result_array();
            
        $data['exp_categories'] = $this->db->get_where('expense_category', "sub_of is NULL")->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('hr/payroll_statutory_rules', $data);
        $this->load->view('inc/footer');
    }

    public function save_rule()
    {
        $id = $this->input->post('id');
        

        $data = array(
            'rule_name'             => $this->input->post('rule_name'),
            'rule_code'             => $this->input->post('rule_code'),
            'rule_type'             => $this->input->post('rule_type'),
            'calculation_base'      => $this->input->post('calculation_base'),
            'status'                => $this->input->post('status'),
            'effective_from'        => $this->input->post('effective_from') ?: NULL,
            'effective_to'          => $this->input->post('effective_to') ?: NULL,
            'expense_category'      => json_encode($this->input->post('expense_category_id')) ?: NULL,
            'updated_at'            => date('Y-m-d H:i:s')
        );

        if ($id) {
            $this->db->where('id', $id);
            $this->db->update('payroll_statutory_rules', $data);
            echo json_encode(array('status' => true, 'message' => 'Rule updated successfully'));
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('payroll_statutory_rules', $data);
            echo json_encode(array('status' => true, 'message' => 'Rule added successfully'));
        }
    }

    public function get_rule($id)
    {
        $rule = $this->db
            ->where('id', $id)
            ->get('payroll_statutory_rules')
            ->row_array();

        echo json_encode($rule);
    }

    public function delete_rule($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('payroll_statutory_rules');

        echo json_encode(array('status' => true, 'message' => 'Rule deleted successfully'));
    }

    public function get_slabs($rule_id)
    {
        $slabs = $this->db
            ->where('rule_id', $rule_id)
            ->order_by('min_salary', 'ASC')
            ->get('payroll_statutory_rule_slabs')
            ->result_array();

        echo json_encode($slabs);
    }

    public function save_slab()
    {
        $id = $this->input->post('id');
        $exps = $this->input->post('expense_category_id');

        $data = array(
            'rule_id'                   => $this->input->post('rule_id'),
            'min_salary'                => $this->input->post('min_salary'),
            'max_salary'                => $this->input->post('max_salary') ?: NULL,

            'employee_applicable'       => $this->input->post('employee_applicable') ? 1 : 0,
            'employee_calculation_type' => $this->input->post('employee_calculation_type'),
            'employee_value'            => $this->input->post('employee_value') ?: 0,

            'employer_applicable'       => $this->input->post('employer_applicable') ? 1 : 0,
            'employer_calculation_type' => $this->input->post('employer_calculation_type'),
            'employer_value'            => $this->input->post('employer_value') ?: 0,
            'expense_category_id'       => json_encode($exps),
            'status'                    => $this->input->post('status'),
            'updated_at'                => date('Y-m-d H:i:s')
        );

        if ($data['employee_applicable'] == 0) {
            $data['employee_calculation_type'] = 'none';
            $data['employee_value'] = 0;
        }

        if ($data['employer_applicable'] == 0) {
            $data['employer_calculation_type'] = 'none';
            $data['employer_value'] = 0;
        }

        if ($id) {
            $this->db->where('id', $id);
            $this->db->update('payroll_statutory_rule_slabs', $data);
            echo json_encode(array('status' => true, 'message' => 'Slab updated successfully'));
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('payroll_statutory_rule_slabs', $data);
            echo json_encode(array('status' => true, 'message' => 'Slab added successfully'));
        }
    }

    public function get_slab($id)
    {
        $slab = $this->db
            ->where('id', $id)
            ->get('payroll_statutory_rule_slabs')
            ->row_array();

        echo json_encode($slab);
    }

    public function delete_slab($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('payroll_statutory_rule_slabs');

        echo json_encode(array('status' => true, 'message' => 'Slab deleted successfully'));
    }
}