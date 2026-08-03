<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reminders_service {

    /** @var CI_Controller */
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function can_manage($user)
    {
        if (!$user) return false;
        if ($user['role'] === 'Admin') return true;
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        return $acc && !empty($acc['reminders_sidebar']);
    }

    public function permissions($user)
    {
        $is_admin = $user && $user['role'] === 'Admin';
        $acc = $is_admin ? array() : ($this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array() ?: array());
        return array(
            'is_admin' => $is_admin,
            'add_rules' => $is_admin || !empty($acc['reminders_add_rules']),
            'all_rules' => $is_admin || !empty($acc['reminders_all_rules']),
            'all_pending' => $is_admin || !empty($acc['reminders_all_pending']),
            'all_completed' => $is_admin || !empty($acc['reminders_all_completed']),
        );
    }

    public function meta($user)
    {
        return array(
            'permissions' => $this->permissions($user),
            'campuses' => $this->_campuses_for_user($user),
            'weekly_days' => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
            'months' => array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'),
        );
    }

    public function campus_users($campus_id)
    {
        $campus_id = (int) $campus_id;
        if ($campus_id <= 0) return array();
        $rows = $this->ci->db
            ->select('user_id, first_name, last_name')
            ->from('users')
            ->where(array('campus_id' => $campus_id, 'status' => 1))
            ->order_by('first_name', 'ASC')
            ->get()->result_array();
        foreach ($rows as &$row) {
            $row['name'] = trim($row['first_name'].' '.$row['last_name']);
        }
        return $rows;
    }

    public function rules_list()
    {
        $rows = $this->ci->db->order_by('reminder_id', 'DESC')->get('reminders')->result_array();
        return $this->_enrich_rules($rows);
    }

    public function pending_list($user, $campus_id = null)
    {
        return $this->_instances_list($user, 0, $campus_id);
    }

    public function completed_list($user, $campus_id = null)
    {
        return $this->_instances_list($user, 1, $campus_id);
    }

    public function insert_reminder($user)
    {
        $user_ids = $this->ci->input->post('user_ids');
        if (!is_array($user_ids) || !count($user_ids)) {
            return array('success' => false, 'message' => 'Select at least one user');
        }

        $type = trim((string) $this->ci->input->post('type'));
        $note = trim((string) $this->ci->input->post('note'));
        if ($note === '') {
            return array('success' => false, 'message' => 'Note is required');
        }

        $image = $this->_upload_image('image');
        if ($image === false) {
            return array('success' => false, 'message' => 'Image upload failed');
        }
        if ($image === null) $image = '';

        $add_by = trim((isset($user['first_name']) ? $user['first_name'] : '').' '.(isset($user['last_name']) ? $user['last_name'] : ''));
        if ($add_by === '') $add_by = 'Admin';

        $this->ci->db->where_in('user_id', array_map('intval', $user_ids));
        $users = $this->ci->db->get('users')->result_array();
        if (empty($users)) {
            return array('success' => false, 'message' => 'No valid users found');
        }

        if ($type === 'once') {
            $once_date = trim((string) $this->ci->input->post('once_date'));
            if ($once_date === '') {
                return array('success' => false, 'message' => 'Reminder date is required');
            }
            foreach ($users as $u) {
                $this->ci->db->insert('reminder', array(
                    'user_id' => $u['user_id'],
                    'date' => $once_date,
                    'note' => $note,
                    'image' => $image,
                    'add_by' => $add_by,
                    'status' => 'Pending',
                ));
            }
        } elseif ($type === 'daily') {
            foreach ($users as $u) {
                $this->ci->db->insert('reminders', array(
                    'type' => $type,
                    'user_id' => $u['user_id'],
                    'note' => $note,
                    'image' => $image,
                    'add_by' => $add_by,
                ));
            }
        } elseif ($type === 'weekly') {
            $weekly_days = $this->ci->input->post('weekly_days');
            if (!is_array($weekly_days) || !count($weekly_days)) {
                return array('success' => false, 'message' => 'Select at least one weekday');
            }
            foreach ($weekly_days as $weekly_day) {
                foreach ($users as $u) {
                    $this->ci->db->insert('reminders', array(
                        'type' => $type,
                        'user_id' => $u['user_id'],
                        'weekly_days' => $weekly_day,
                        'note' => $note,
                        'image' => $image,
                        'add_by' => $add_by,
                    ));
                }
            }
        } elseif ($type === 'monthly') {
            $monthly_dates = $this->ci->input->post('monthly_dates');
            if (!is_array($monthly_dates) || !count($monthly_dates)) {
                return array('success' => false, 'message' => 'Select at least one date');
            }
            foreach ($monthly_dates as $monthly_date) {
                foreach ($users as $u) {
                    $this->ci->db->insert('reminders', array(
                        'type' => $type,
                        'user_id' => $u['user_id'],
                        'monthly_dates' => $monthly_date,
                        'note' => $note,
                        'image' => $image,
                        'add_by' => $add_by,
                    ));
                }
            }
        } elseif ($type === 'yearly') {
            $yearly_date = trim((string) $this->ci->input->post('yearly_date'));
            $yearly_month = trim((string) $this->ci->input->post('yearly_month'));
            foreach ($users as $u) {
                $this->ci->db->insert('reminders', array(
                    'type' => $type,
                    'user_id' => $u['user_id'],
                    'yearly_date' => $yearly_date,
                    'yearly_month' => $yearly_month,
                    'note' => $note,
                    'image' => $image,
                    'add_by' => $add_by,
                ));
            }
        } else {
            return array('success' => false, 'message' => 'Invalid reminder type');
        }

        return array('success' => true, 'message' => 'Reminder assigned successfully');
    }

    public function delete_rule($id)
    {
        $id = (int) $id;
        if ($id <= 0) return array('success' => false, 'message' => 'Invalid id');
        $this->ci->db->delete('reminders', array('reminder_id' => $id));
        return array('success' => true);
    }

    public function delete_instance($id)
    {
        $id = (int) $id;
        if ($id <= 0) return array('success' => false, 'message' => 'Invalid id');
        $this->ci->db->delete('reminder', array('reminder_id' => $id));
        return array('success' => true);
    }

    public function approve_instance($id)
    {
        $id = (int) $id;
        $row = $this->ci->db->get_where('reminder', array('reminder_id' => $id))->row_array();
        if (!$row) return array('success' => false, 'message' => 'Not found');
        $this->ci->db->set('check_by_admin', 1);
        $this->ci->db->where('reminder_id', $id);
        $this->ci->db->update('reminder');
        return array('success' => true);
    }

    // --- Private ---

    private function _campuses_for_user($user)
    {
        if ($user && $user['role'] === 'Admin') {
            return $this->ci->db->order_by('campus_name', 'ASC')->get('campuses')->result_array();
        }
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        $ids = ($acc && !empty($acc['campus_ids']))
            ? array_filter(array_map('intval', explode(',', $acc['campus_ids'])))
            : array();
        if (!count($ids)) return array();
        return $this->ci->db->where_in('campus_id', $ids)->order_by('campus_name', 'ASC')->get('campuses')->result_array();
    }

    private function _campus_scope_ids($user)
    {
        if (!$user || $user['role'] === 'Admin') return null;
        $acc = $this->ci->db->get_where('access', array('user_id' => $user['user_id']))->row_array();
        return ($acc && !empty($acc['campus_ids']))
            ? array_filter(array_map('intval', explode(',', $acc['campus_ids'])))
            : array(0);
    }

    private function _instances_list($user, $check_by_admin, $campus_id = null)
    {
        $this->ci->db->select('reminder.*, users.first_name, users.last_name, users.campus_id, campuses.campus_name');
        $this->ci->db->from('reminder');
        $this->ci->db->join('users', 'users.user_id=reminder.user_id', 'INNER');
        $this->ci->db->join('campuses', 'users.campus_id=campuses.campus_id', 'INNER');
        if ($campus_id !== null && $campus_id !== '') {
            $this->ci->db->where('campuses.campus_id', (int) $campus_id);
        }
        $this->ci->db->where('reminder.check_by_admin', (int) $check_by_admin);
        $scope = $this->_campus_scope_ids($user);
        if ($scope !== null) {
            $this->ci->db->where_in('campuses.campus_id', $scope);
        }
        $this->ci->db->order_by('reminder.reminder_id', 'DESC');
        $rows = $this->ci->db->get()->result_array();
        return $this->_enrich_instances($rows);
    }

    private function _enrich_rules($rows)
    {
        $userCache = array();
        $out = array();
        foreach ($rows as $row) {
            $uid = (int) $row['user_id'];
            if (!isset($userCache[$uid])) {
                $u = $this->ci->db->select('first_name, last_name')->get_where('users', array('user_id' => $uid))->row_array();
                $userCache[$uid] = $u ? trim($u['first_name'].' '.$u['last_name']) : '—';
            }
            $row['user_name'] = $userCache[$uid];
            $row['alert_on'] = $this->_rule_alert_label($row);
            $row['image_url'] = $this->_image_url(isset($row['image']) ? $row['image'] : '', '');
            $out[] = $row;
        }
        return $out;
    }

    private function _enrich_instances($rows)
    {
        $out = array();
        foreach ($rows as $row) {
            $row['user_name'] = trim($row['first_name'].' '.$row['last_name']);
            $online = isset($row['online_image']) ? $row['online_image'] : '';
            $row['image_url'] = $this->_image_url(isset($row['image']) ? $row['image'] : '', $online);
            if ($row['status'] === 'Completed' && (int) $row['check_by_admin'] === 0) {
                $row['status_label'] = 'Completed (Under Review)';
            } else {
                $row['status_label'] = $row['status'];
            }
            $out[] = $row;
        }
        return $out;
    }

    private function _rule_alert_label($row)
    {
        $type = isset($row['type']) ? $row['type'] : '';
        if ($type === 'once') return isset($row['once_date']) ? $row['once_date'] : '—';
        if ($type === 'daily') return 'Every day';
        if ($type === 'weekly') return isset($row['weekly_days']) ? $row['weekly_days'] : '—';
        if ($type === 'monthly') return isset($row['monthly_dates']) ? $row['monthly_dates'] : '—';
        if ($type === 'yearly') {
            return trim((isset($row['yearly_date']) ? $row['yearly_date'] : '').' '.(isset($row['yearly_month']) ? $row['yearly_month'] : ''));
        }
        return '—';
    }

    private function _image_url($image, $online_image)
    {
        if ($online_image !== '' && $online_image !== null) {
            return $online_image;
        }
        if ($image !== '' && $image !== null) {
            return base_url().'reminder_images/'.$image;
        }
        return '';
    }

    private function _upload_image($field)
    {
        if (empty($_FILES[$field]['name'])) return null;
        $dir = FCPATH.'reminder_images/';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        $this->ci->load->library('upload');
        $config = array(
            'upload_path' => $dir,
            'allowed_types' => 'gif|jpg|jpeg|png',
        );
        $this->ci->upload->initialize($config);
        if (!$this->ci->upload->do_upload($field)) return false;
        $data = $this->ci->upload->data();
        return isset($data['file_name']) ? $data['file_name'] : false;
    }
}
