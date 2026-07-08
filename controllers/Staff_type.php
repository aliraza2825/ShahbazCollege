<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Staff_type extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
        $this->ensure_staff_timing_columns();
	}

    private function ensure_staff_timing_columns()
    {
        if ($this->db->table_exists('staff_timing') && !$this->db->field_exists('staff_type_id', 'staff_timing')) {
            $this->db->query("ALTER TABLE staff_timing ADD staff_type_id INT NULL AFTER staff_id");
        }
    }

    private function week_days()
    {
        return array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
    }
	
	public function add_staff_type()
	{
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('staff_type/add_staff_type');
		$this->load->view('inc/footer');
	}
	
	public function insert()
	{
		$staff_type_name = $this->input->post('staff_type_name');
		$check = $this->db->get_where('staff_type',array('staff_type_name'=>$staff_type_name))->result_array();
		
		if(count($check)>0)
		{
			$this->session->set_flashdata('error','Staff Type Already Added.');
			redirect('staff_type/add_staff_type');
		}
		else
		{
			$this->db->set('staff_type_name',$staff_type_name);
			$this->db->insert('staff_type');
			
			$this->session->set_flashdata('message','Staff Type Added Successfully');
			redirect('staff_type/add_staff_type');
		}
	}
	
	public function all_staff_type()
	{
		$data['staff_types'] = $this->db->get('staff_type')->result_array();
        $timingRows = $this->db
            ->select('staff_type_id, COUNT(*) as total')
            ->where('staff_type_id IS NOT NULL', null, false)
            ->group_by('staff_type_id')
            ->get('staff_timing')
            ->result_array();
        $data['timing_map'] = array();
        foreach ($timingRows as $timingRow) {
            $data['timing_map'][$timingRow['staff_type_id']] = (int) $timingRow['total'];
        }
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('staff_type/all_staff_type',$data);
		$this->load->view('inc/footer');
	}
	
	public function edit_staff_type($staff_type_id)
	{
		$data['staff_type'] = $this->db->get_where('staff_type',array('staff_type_id'=>$staff_type_id))->result_array();
		
		$this->load->view('inc/header');
		$this->load->view('inc/sidebar');
		$this->load->view('staff_type/edit_staff_type',$data);
		$this->load->view('inc/footer');
	}
	
	public function update($staff_type_id)
	{
		$staff_type_name = $this->input->post('staff_type_name');
		
		$this->db->set('staff_type_name',$staff_type_name);
		$this->db->where('staff_type_id',$staff_type_id);
		$this->db->update('staff_type');
		
		$this->session->set_flashdata('message','Staff Type Updated Successfully');
		redirect('staff_type/edit_staff_type/'.$staff_type_id);
	}
	
	public function delete($staff_type_id)
	{
		$this->db->where('staff_type_id',$staff_type_id);
		$this->db->delete('staff_type');
		
		$this->session->set_flashdata('message','Staff Type Deleted Successfully');
		redirect('staff_type/all_staff_type');
	}

    public function staff_timing($staff_type_id)
    {
        $staffType = $this->db->get_where('staff_type', array('staff_type_id' => $staff_type_id))->row_array();
        if (!$staffType) {
            show_404();
        }

        $timings = $this->db
            ->where('staff_type_id', $staff_type_id)
            ->get('staff_timing')
            ->result_array();

        $timingMap = array();
        foreach ($timings as $timing) {
            $timingMap[$timing['day']] = $timing;
        }

        $data['staff_type'] = $staffType;
        $data['week_days'] = $this->week_days();
        $data['timings'] = $timingMap;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('staff_type/staff_timing', $data);
        $this->load->view('inc/footer');
    }

    public function save_staff_timing($staff_type_id)
    {
        $staffType = $this->db->get_where('staff_type', array('staff_type_id' => $staff_type_id))->row_array();
        if (!$staffType) {
            show_404();
        }

        $days = $this->input->post('day');
        $checkin = $this->input->post('checkin_time');
        $checkout = $this->input->post('checkout_time');
        $halfDayOn = $this->input->post('half_day_on');
        $fullDayOn = $this->input->post('full_day_on');

        if (!is_array($days)) {
            $days = array();
        }

        $count = count($days);
        for ($i = 0; $i < $count; $i++) {
            $day = isset($days[$i]) ? $days[$i] : '';
            if ($day === '') {
                continue;
            }

            $checkStaffEntry = $this->db
                ->where('staff_type_id', $staff_type_id)
                ->where('day', $day)
                ->get('staff_timing')
                ->row_array();

            $payload = array(
                'day' => $day,
                'checkin_timing' => isset($checkin[$i]) ? $checkin[$i] : '00:00:00',
                'checkout_timing' => isset($checkout[$i]) ? $checkout[$i] : '00:00:00',
                'half_day_on' => isset($halfDayOn[$i]) ? $halfDayOn[$i] : '00:00:00',
                'full_day_on' => isset($fullDayOn[$i]) ? $fullDayOn[$i] : '00:00:00',
                'staff_type_id' => $staff_type_id,
                'staff_id' => 0
            );

            if ($checkStaffEntry) {
                $this->db->where('id', $checkStaffEntry['id'])->update('staff_timing', $payload);
            } else {
                $this->db->insert('staff_timing', $payload);
            }
        }

        $this->session->set_flashdata('message', 'Staff Type Timing Updated Successfully.');
        redirect('staff_type/staff_timing/'.$staff_type_id);
    }

    public function delete_staff_timing($staff_type_id)
    {
        $this->db->where('staff_type_id', $staff_type_id);
        $this->db->delete('staff_timing');

        $this->session->set_flashdata('message', 'Staff Type Timing Deleted Successfully.');
        redirect('staff_type/all_staff_type');
    }
		
}
