<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payroll_income_tax extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('account');
    }

    public function index()
    {
        $data['tax_years'] = $this->db
            ->order_by('id', 'DESC')
            ->get('payroll_tax_years')
            ->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('hr/payroll_income_tax', $data);
        $this->load->view('inc/footer');
    }

    public function save_tax_year()
    {
        $id = $this->input->post('id');

        $data = array(
            'tax_year'   => $this->input->post('tax_year'),
            'start_date' => $this->input->post('start_date'),
            'end_date'   => $this->input->post('end_date'),
            'status'     => $this->input->post('status'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        if ($id) {
            $this->db->where('id', $id);
            $this->db->update('payroll_tax_years', $data);

            echo json_encode(array('status' => true, 'message' => 'Tax year updated successfully'));
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');

            $this->db->insert('payroll_tax_years', $data);

            echo json_encode(array('status' => true, 'message' => 'Tax year added successfully'));
        }
    }

    public function get_tax_year($id)
    {
        $tax_year = $this->db
            ->where('id', $id)
            ->get('payroll_tax_years')
            ->row_array();

        echo json_encode($tax_year);
    }

    public function delete_tax_year($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('payroll_tax_years');

        echo json_encode(array('status' => true, 'message' => 'Tax year deleted successfully'));
    }

    public function get_slabs($tax_year_id)
    {
        $slabs = $this->db
            ->where('tax_year_id', $tax_year_id)
            ->order_by('min_annual_income', 'ASC')
            ->get('payroll_income_tax_slabs')
            ->result_array();

        echo json_encode($slabs);
    }

    public function save_slab()
    {
        $id = $this->input->post('id');

        $data = array(
            'tax_year_id'          => $this->input->post('tax_year_id'),
            'min_annual_income'   => $this->input->post('min_annual_income') ?: 0,
            'max_annual_income'   => $this->input->post('max_annual_income') ?: NULL,
            'fixed_tax'           => $this->input->post('fixed_tax') ?: 0,
            'taxable_amount_above'=> $this->input->post('taxable_amount_above') ?: 0,
            'tax_percentage'      => $this->input->post('tax_percentage') ?: 0,
            'status'              => $this->input->post('status'),
            'updated_at'          => date('Y-m-d H:i:s')
        );

        if ($id) {
            $this->db->where('id', $id);
            $this->db->update('payroll_income_tax_slabs', $data);

            echo json_encode(array('status' => true, 'message' => 'Tax slab updated successfully'));
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');

            $this->db->insert('payroll_income_tax_slabs', $data);

            echo json_encode(array('status' => true, 'message' => 'Tax slab added successfully'));
        }
    }

    public function get_slab($id)
    {
        $slab = $this->db
            ->where('id', $id)
            ->get('payroll_income_tax_slabs')
            ->row_array();

        echo json_encode($slab);
    }

    public function delete_slab($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('payroll_income_tax_slabs');

        echo json_encode(array('status' => true, 'message' => 'Tax slab deleted successfully'));
    }
    
    public function tax_year_report()
    {
        $tax_year_id = $this->input->post('tax_year_id');
    
        $data['tax_years'] = $this->db
            ->where('status', 1)
            ->order_by('id', 'DESC')
            ->get('payroll_tax_years')
            ->result_array();
    
        $data['report'] = array();
        $data['selected_tax_year_id'] = $tax_year_id;
        $data['tax_year'] = array();
    
        if ($tax_year_id) {
    
            $tax_year = $this->db
                ->where('id', $tax_year_id)
                ->get('payroll_tax_years')
                ->row_array();
    
            $data['tax_year'] = $tax_year;
    
            if ($tax_year) {
    
                $start = strtotime($tax_year['start_date']);
                $end   = strtotime($tax_year['end_date']);
    
                $this->db->select('
                    p.*,
                    u.first_name,
                    u.last_name,
                    u.cnic,
                    u.mobile,
                    c.campus_name
                ');
    
                $this->db->from('payroll p');
                $this->db->join('users u', 'u.user_id = p.user_id', 'left');
                $this->db->join('campuses c', 'c.campus_id = u.campus_id', 'left');
    
                /*
                 * Payroll month/year ko date bana kar tax year range mein filter karna
                 * Agar payroll_month Apr/April/04/4 kisi bhi format mein ho,
                 * neeche PHP side per bhi final filter kar rahe hain.
                 */
                $this->db->where('p.tax >', 0);
                $this->db->order_by('u.first_name', 'ASC');
                $this->db->order_by('p.payroll_year', 'ASC');
    
                $payrolls = $this->db->get()->result_array();
    
                $report = array();
    
                foreach ($payrolls as $p) {
    
                    $month_no = $this->get_month_number_from_payroll($p['payroll_month']);
    
                    if (!$month_no) {
                        continue;
                    }
    
                    $payroll_date = date('Y-m-t', strtotime($p['payroll_year'] . '-' . $month_no . '-01'));
    
                    if (strtotime($payroll_date) < $start || strtotime($payroll_date) > $end) {
                        continue;
                    }
    
                    $user_id = $p['user_id'];
    
                    if (!isset($report[$user_id])) {
                        $report[$user_id] = array(
                            'user_id' => $user_id,
                            'employee_name' => trim($p['first_name'] . ' ' . $p['last_name']),
                            'cnic' => $p['cnic'],
                            'mobile' => $p['mobile'],
                            'college_name' => $p['campus_name'],
    
                            'total_salary_amount' => 0,
                            'total_salary_paid' => 0,
                            'total_tax' => 0,
    
                            'details' => array()
                        );
                    }
    
                    $on_salary_amount = (float)$p['basic_salary'] + (float)$p['earnings'];
    
                    if ($on_salary_amount <= 0) {
                        $on_salary_amount = (float)$p['gross_salary'];
                    }
    
                    $salary_paid = (float)$p['earned_salary'];
                    $tax_amount  = (float)$p['tax'];
    
                    $report[$user_id]['total_salary_amount'] += $on_salary_amount;
                    $report[$user_id]['total_salary_paid'] += $salary_paid;
                    $report[$user_id]['total_tax'] += $tax_amount;
    
                    $report[$user_id]['details'][] = array(
                        'month' => date('F Y', strtotime($payroll_date)),
                        'basic_salary' => (float)$p['basic_salary'],
                        'earnings' => (float)$p['earnings'],
                        'gross_salary' => (float)$p['gross_salary'],
                        'on_salary_amount' => $on_salary_amount,
                        'salary_paid' => $salary_paid,
                        'tax_amount' => $tax_amount,
                        'payroll_id' => $p['id']
                    );
                }
    
                $data['report'] = array_values($report);
            }
        }
    
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('hr/tax_year_report', $data);
        $this->load->view('inc/footer');
    }
    
    private function get_month_number_from_payroll($month)
    {
        $month = trim($month);
    
        if ($month == '') {
            return false;
        }
    
        if (is_numeric($month)) {
            return str_pad((int)$month, 2, '0', STR_PAD_LEFT);
        }
    
        $month = strtolower($month);
    
        $months = array(
            'jan' => '01',
            'january' => '01',
            'feb' => '02',
            'february' => '02',
            'mar' => '03',
            'march' => '03',
            'apr' => '04',
            'april' => '04',
            'may' => '05',
            'jun' => '06',
            'june' => '06',
            'jul' => '07',
            'july' => '07',
            'aug' => '08',
            'august' => '08',
            'sep' => '09',
            'sept' => '09',
            'september' => '09',
            'oct' => '10',
            'october' => '10',
            'nov' => '11',
            'november' => '11',
            'dec' => '12',
            'december' => '12'
        );
    
        return isset($months[$month]) ? $months[$month] : false;
    }
}