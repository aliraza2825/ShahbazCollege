<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff_shifts extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        ensure_staff_shift_schema();
    }

    private function week_days()
    {
        return array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
    }

    private function load_study_types()
    {
        if (!$this->db->table_exists('study_type')) {
            return array();
        }

        return $this->db->order_by('name', 'ASC')->get('study_type')->result_array();
    }

    private function shift_combo_exists($shiftName, $studyTypeId, $excludeId = 0)
    {
        $this->db->where('shift_name', $shiftName);
        $this->db->where('study_type_id', (int) $studyTypeId);
        if ($excludeId > 0) {
            $this->db->where('staff_shift_id !=', (int) $excludeId);
        }
        return count($this->db->get('staff_shifts')->result_array()) > 0;
    }

    public function add_staff_shift()
    {
        $data['study_types'] = $this->load_study_types();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('staff_shifts/add_staff_shift', $data);
        $this->load->view('inc/footer');
    }

    public function insert()
    {
        $shiftName = trim((string) $this->input->post('shift_name'));
        $studyTypeId = (int) $this->input->post('study_type_id');

        if ($studyTypeId <= 0) {
            $this->session->set_flashdata('error', 'Please select study type.');
            redirect('staff_shifts/add_staff_shift');
        }

        if ($this->shift_combo_exists($shiftName, $studyTypeId)) {
            $this->session->set_flashdata('error', 'This shift and study type combination already exists.');
            redirect('staff_shifts/add_staff_shift');
        }

        $this->db->set('shift_name', $shiftName);
        $this->db->set('study_type_id', $studyTypeId);
        $this->db->set('description', $this->input->post('description'));
        $this->db->set('status', (int) $this->input->post('status'));
        $this->db->set('created_at', date('Y-m-d H:i:s'));
        $this->db->insert('staff_shifts');

        $this->session->set_flashdata('message', 'Staff Shift Added Successfully');
        redirect('staff_shifts/add_staff_shift');
    }

    public function all_staff_shifts()
    {
        $data['staff_shifts'] = get_staff_shifts_with_study_type(false);
        $timingRows = $this->db
            ->select('staff_shift_id, COUNT(*) as total')
            ->where('staff_shift_id IS NOT NULL', null, false)
            ->group_by('staff_shift_id')
            ->get('staff_timing')
            ->result_array();

        $data['timing_map'] = array();
        foreach ($timingRows as $timingRow) {
            $data['timing_map'][$timingRow['staff_shift_id']] = (int) $timingRow['total'];
        }

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('staff_shifts/all_staff_shifts', $data);
        $this->load->view('inc/footer');
    }

    public function edit_staff_shift($staff_shift_id)
    {
        $this->db->select('staff_shifts.*, study_type.name as study_type_name');
        $this->db->from('staff_shifts');
        $this->db->join('study_type', 'study_type.id = staff_shifts.study_type_id', 'left');
        $this->db->where('staff_shifts.staff_shift_id', $staff_shift_id);
        $data['staff_shift'] = $this->db->get()->result_array();
        if (count($data['staff_shift']) <= 0) {
            show_404();
        }
        $data['study_types'] = $this->load_study_types();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('staff_shifts/edit_staff_shift', $data);
        $this->load->view('inc/footer');
    }

    public function update($staff_shift_id)
    {
        $shiftName = trim((string) $this->input->post('shift_name'));
        $studyTypeId = (int) $this->input->post('study_type_id');

        if ($studyTypeId <= 0) {
            $this->session->set_flashdata('error', 'Please select study type.');
            redirect('staff_shifts/edit_staff_shift/'.$staff_shift_id);
        }

        if ($this->shift_combo_exists($shiftName, $studyTypeId, $staff_shift_id)) {
            $this->session->set_flashdata('error', 'This shift and study type combination already exists.');
            redirect('staff_shifts/edit_staff_shift/'.$staff_shift_id);
        }

        $this->db->set('shift_name', $shiftName);
        $this->db->set('study_type_id', $studyTypeId);
        $this->db->set('description', $this->input->post('description'));
        $this->db->set('status', (int) $this->input->post('status'));
        $this->db->set('updated_at', date('Y-m-d H:i:s'));
        $this->db->where('staff_shift_id', $staff_shift_id);
        $this->db->update('staff_shifts');

        $this->session->set_flashdata('message', 'Staff Shift Updated Successfully');
        redirect('staff_shifts/edit_staff_shift/'.$staff_shift_id);
    }

    public function delete($staff_shift_id)
    {
        $this->db->where('staff_shift_id', $staff_shift_id);
        $this->db->delete('staff_timing');

        $this->db->where('staff_shift_id', $staff_shift_id);
        $this->db->delete('staff_shifts');

        $this->session->set_flashdata('message', 'Staff Shift Deleted Successfully');
        redirect('staff_shifts/all_staff_shifts');
    }

    public function staff_timing($staff_shift_id)
    {
        $this->db->select('staff_shifts.*, study_type.name as study_type_name');
        $this->db->from('staff_shifts');
        $this->db->join('study_type', 'study_type.id = staff_shifts.study_type_id', 'left');
        $this->db->where('staff_shifts.staff_shift_id', $staff_shift_id);
        $staffShift = $this->db->get()->row_array();
        if (!$staffShift) {
            show_404();
        }

        $timings = $this->db
            ->where('staff_shift_id', $staff_shift_id)
            ->get('staff_timing')
            ->result_array();

        $timingMap = array();
        foreach ($timings as $timing) {
            $timingMap[$timing['day']] = $timing;
        }

        $data['staff_shift'] = $staffShift;
        $data['week_days'] = $this->week_days();
        $data['timings'] = $timingMap;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('staff_shifts/staff_timing', $data);
        $this->load->view('inc/footer');
    }

    public function save_staff_timing($staff_shift_id)
    {
        $staffShift = $this->db->get_where('staff_shifts', array('staff_shift_id' => $staff_shift_id))->row_array();
        if (!$staffShift) {
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
                ->where('staff_shift_id', $staff_shift_id)
                ->where('day', $day)
                ->get('staff_timing')
                ->row_array();

            $payload = array(
                'day' => $day,
                'checkin_timing' => isset($checkin[$i]) ? $checkin[$i] : '00:00:00',
                'checkout_timing' => isset($checkout[$i]) ? $checkout[$i] : '00:00:00',
                'half_day_on' => isset($halfDayOn[$i]) ? $halfDayOn[$i] : '00:00:00',
                'full_day_on' => isset($fullDayOn[$i]) ? $fullDayOn[$i] : '00:00:00',
                'staff_shift_id' => $staff_shift_id,
                'staff_id' => 0
            );

            if ($checkStaffEntry) {
                $this->db->where('id', $checkStaffEntry['id'])->update('staff_timing', $payload);
            } else {
                $this->db->insert('staff_timing', $payload);
            }
        }

        $this->session->set_flashdata('message', 'Staff Shift Timing Updated Successfully.');
        redirect('staff_shifts/staff_timing/'.$staff_shift_id);
    }

    public function delete_staff_timing($staff_shift_id)
    {
        $this->db->where('staff_shift_id', $staff_shift_id);
        $this->db->delete('staff_timing');

        $this->session->set_flashdata('message', 'Staff Shift Timing Deleted Successfully.');
        redirect('staff_shifts/all_staff_shifts');
    }
}
