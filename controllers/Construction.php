<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Construction extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('upload');
        $this->ensure_tables();
    }

    private function ensure_tables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS construction_projects (
            id INT NOT NULL AUTO_INCREMENT,
            project_name VARCHAR(255) NOT NULL,
            location VARCHAR(255) NULL,
            client VARCHAR(255) NULL,
            start_date DATE NULL,
            expected_completion_date DATE NULL,
            budget DECIMAL(15,2) NOT NULL DEFAULT 0,
            status VARCHAR(30) NOT NULL DEFAULT 'Planning',
            project_manager_id INT NULL,
            created_by INT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY status (status),
            KEY project_manager_id (project_manager_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS construction_boq (
            id INT NOT NULL AUTO_INCREMENT,
            project_id INT NOT NULL,
            work_item VARCHAR(255) NOT NULL,
            quantity DECIMAL(15,2) NOT NULL DEFAULT 0,
            unit VARCHAR(50) NULL,
            unit_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
            total_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
            estimated_budget DECIMAL(15,2) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS construction_material_issues (
            id INT NOT NULL AUTO_INCREMENT,
            project_id INT NOT NULL,
            product_id INT NOT NULL,
            product_name_id INT NULL,
            campus_id INT NULL,
            room_id INT NULL,
            subroom_id INT NULL,
            quantity DECIMAL(15,2) NOT NULL DEFAULT 0,
            unit_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
            total_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
            issue_date DATE NOT NULL,
            issued_by INT NULL,
            remarks TEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY product_id (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS construction_labours (
            id INT NOT NULL AUTO_INCREMENT,
            project_id INT NULL,
            labour_name VARCHAR(255) NOT NULL,
            cnic VARCHAR(30) NULL,
            mobile VARCHAR(30) NULL,
            designation VARCHAR(100) NULL,
            daily_wage DECIMAL(15,2) NOT NULL DEFAULT 0,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS construction_labour_attendance (
            id INT NOT NULL AUTO_INCREMENT,
            labour_id INT NOT NULL,
            project_id INT NOT NULL,
            attendance_date DATE NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'Present',
            overtime_hours DECIMAL(10,2) NOT NULL DEFAULT 0,
            overtime_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY labour_id (labour_id),
            KEY project_id (project_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS construction_labour_advances (
            id INT NOT NULL AUTO_INCREMENT,
            labour_id INT NOT NULL,
            project_id INT NOT NULL,
            advance_date DATE NOT NULL,
            amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            remarks TEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY labour_id (labour_id),
            KEY project_id (project_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS construction_labour_payroll (
            id INT NOT NULL AUTO_INCREMENT,
            labour_id INT NOT NULL,
            project_id INT NOT NULL,
            payroll_month VARCHAR(20) NULL,
            payable_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            remarks TEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY labour_id (labour_id),
            KEY project_id (project_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS construction_contractors (
            id INT NOT NULL AUTO_INCREMENT,
            project_id INT NOT NULL,
            contractor_name VARCHAR(255) NOT NULL,
            contact_details TEXT NULL,
            contract_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            advance_payment DECIMAL(15,2) NOT NULL DEFAULT 0,
            running_bills DECIMAL(15,2) NOT NULL DEFAULT 0,
            final_bill DECIMAL(15,2) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS construction_contractor_payments (
            id INT NOT NULL AUTO_INCREMENT,
            contractor_id INT NOT NULL,
            project_id INT NOT NULL,
            payment_date DATE NOT NULL,
            amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            payment_type VARCHAR(50) NULL,
            remarks TEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY contractor_id (contractor_id),
            KEY project_id (project_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS construction_site_expenses (
            id INT NOT NULL AUTO_INCREMENT,
            project_id INT NOT NULL,
            category VARCHAR(100) NOT NULL,
            expense_date DATE NOT NULL,
            amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            description TEXT NULL,
            attachment VARCHAR(255) NULL,
            created_by INT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY category (category)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS construction_equipment (
            id INT NOT NULL AUTO_INCREMENT,
            project_id INT NULL,
            equipment_name VARCHAR(255) NOT NULL,
            operator VARCHAR(255) NULL,
            fuel_consumption DECIMAL(15,2) NOT NULL DEFAULT 0,
            maintenance_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
            repair_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
            usage_history TEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS construction_progress (
            id INT NOT NULL AUTO_INCREMENT,
            project_id INT NOT NULL,
            milestone VARCHAR(255) NULL,
            foundation_percent DECIMAL(6,2) NOT NULL DEFAULT 0,
            structure_percent DECIMAL(6,2) NOT NULL DEFAULT 0,
            finishing_percent DECIMAL(6,2) NOT NULL DEFAULT 0,
            overall_percent DECIMAL(6,2) NOT NULL DEFAULT 0,
            remarks TEXT NULL,
            progress_date DATE NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS construction_cost_entries (
            id INT NOT NULL AUTO_INCREMENT,
            project_id INT NOT NULL,
            entry_type VARCHAR(50) NOT NULL,
            entry_date DATE NOT NULL,
            amount DECIMAL(15,2) NOT NULL DEFAULT 0,
            description TEXT NULL,
            created_by INT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY project_id (project_id),
            KEY entry_type (entry_type),
            KEY entry_date (entry_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }

    private function page($section, $data = array())
    {
        $data['section'] = $section;
        $data['projects'] = $this->db->order_by('project_name', 'ASC')->get('construction_projects')->result_array();
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('construction/module', $data);
        $this->load->view('inc/footer');
    }

    private function sum_table($table, $field = 'amount')
    {
        $row = $this->db->select_sum($field)->get($table)->row_array();
        return (float) @$row[$field];
    }

    private function sum_simple_entries($type = null, $projectId = null)
    {
        $this->db->select_sum('amount');
        if ($type !== null) {
            $this->db->where('entry_type', $type);
        }
        if ($projectId !== null) {
            $this->db->where('project_id', $projectId);
        }
        $row = $this->db->get('construction_cost_entries')->row_array();
        return (float) @$row['amount'];
    }

    private function recent_cost_entries($limit = 10)
    {
        $generic = $this->db
            ->select("construction_cost_entries.entry_date as date, construction_cost_entries.entry_type as type, construction_cost_entries.amount, construction_cost_entries.description, construction_projects.project_name, '' as product_name, 0 as quantity", false)
            ->join('construction_projects', 'construction_projects.id = construction_cost_entries.project_id', 'left')
            ->order_by('construction_cost_entries.id', 'DESC')
            ->limit($limit)
            ->get('construction_cost_entries')
            ->result_array();

        $material = $this->db
            ->select("construction_material_issues.issue_date as date, 'Material' as type, construction_material_issues.total_cost as amount, construction_material_issues.remarks as description, construction_projects.project_name, product_names.product_name, construction_material_issues.quantity", false)
            ->join('construction_projects', 'construction_projects.id = construction_material_issues.project_id', 'left')
            ->join('product_names', 'product_names.product_name_id = construction_material_issues.product_name_id', 'left')
            ->order_by('construction_material_issues.id', 'DESC')
            ->limit($limit)
            ->get('construction_material_issues')
            ->result_array();

        $rows = array_merge($generic, $material);
        usort($rows, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });
        return array_slice($rows, 0, $limit);
    }

    private function contractor_summary()
    {
        $contractors = $this->db
            ->select('construction_contractors.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_contractors.project_id', 'left')
            ->order_by('construction_contractors.id', 'DESC')
            ->get('construction_contractors')
            ->result_array();

        foreach ($contractors as $key => $contractor) {
            $paidRow = $this->db
                ->select_sum('amount')
                ->where('contractor_id', $contractor['id'])
                ->get('construction_contractor_payments')
                ->row_array();
            $paid = (float) @$paidRow['amount'];
            $doneAmount = (float) $contractor['final_bill'] > 0 ? (float) $contractor['final_bill'] : (float) $contractor['contract_amount'];
            $contractors[$key]['done_amount'] = $doneAmount;
            $contractors[$key]['paid_amount'] = $paid;
            $contractors[$key]['remaining_amount'] = $doneAmount - $paid;
        }

        return $contractors;
    }

    public function index()
    {
        $data['total_projects'] = $this->db->count_all('construction_projects');
        $data['total_budget'] = $this->sum_table('construction_projects', 'budget');
        $data['material_cost'] = $this->sum_table('construction_material_issues', 'total_cost');
        $data['labour_cost'] = $this->sum_table('construction_labour_payroll', 'paid_amount') + $this->sum_simple_entries('Labour');
        $data['contractor_cost'] = $this->sum_table('construction_contractor_payments', 'amount') + $this->sum_simple_entries('Contractor');
        $data['site_expense'] = $this->sum_table('construction_site_expenses', 'amount') + $this->sum_simple_entries('Site Expense');
        $data['equipment_cost'] = $this->sum_table('construction_equipment', 'maintenance_cost') + $this->sum_table('construction_equipment', 'repair_cost') + $this->sum_simple_entries('Equipment');
        $data['total_expenses'] = $data['material_cost'] + $data['labour_cost'] + $data['contractor_cost'] + $data['site_expense'] + $data['equipment_cost'];
        $data['recent_expenses'] = $this->recent_cost_entries(10);
        $data['low_stock'] = $this->db
            ->select('product_names.product_name, COUNT(products.product_id) as stock_qty')
            ->from('products')
            ->join('product_names', 'product_names.product_name_id = products.product_name_id', 'left')
            ->where('products.status', 1)
            ->where('products.consume', 0)
            ->where('products.sold', 0)
            ->group_by('products.product_name_id')
            ->having('stock_qty <=', 5)
            ->order_by('stock_qty', 'ASC')
            ->limit(10)
            ->get()
            ->result_array();
        $data['latest_progress'] = $this->db
            ->select('construction_progress.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_progress.project_id', 'left')
            ->order_by('construction_progress.progress_date', 'DESC')
            ->limit(10)
            ->get('construction_progress')
            ->result_array();
        $this->page('dashboard', $data);
    }

    public function projects()
    {
        $data['users'] = $this->db->where('status', 1)->order_by('first_name', 'ASC')->get('users')->result_array();
        $data['project_costs'] = $this->project_cost_rows();
        $this->page('projects', $data);
    }

    public function save_project()
    {
        $id = (int) $this->input->post('id');
        $data = array(
            'project_name' => $this->input->post('project_name'),
            'location' => $this->input->post('location'),
            'client' => $this->input->post('client'),
            'start_date' => $this->input->post('start_date'),
            'expected_completion_date' => $this->input->post('expected_completion_date'),
            'budget' => (float) $this->input->post('budget'),
            'status' => $this->input->post('status'),
            'project_manager_id' => (int) $this->input->post('project_manager_id'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        if ($id > 0) {
            $this->db->where('id', $id)->update('construction_projects', $data);
        } else {
            $data['created_by'] = $this->session->userdata('user_id');
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('construction_projects', $data);
        }
        $this->session->set_flashdata('message', 'Project saved successfully.');
        redirect('construction/projects');
    }

    public function boq()
    {
        $data['boq'] = $this->db
            ->select('construction_boq.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_boq.project_id', 'left')
            ->order_by('construction_boq.id', 'DESC')
            ->get('construction_boq')
            ->result_array();
        $this->page('boq', $data);
    }

    public function save_boq()
    {
        $quantity = (float) $this->input->post('quantity');
        $unitCost = (float) $this->input->post('unit_cost');
        $this->db->insert('construction_boq', array(
            'project_id' => (int) $this->input->post('project_id'),
            'work_item' => $this->input->post('work_item'),
            'quantity' => $quantity,
            'unit' => $this->input->post('unit'),
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'estimated_budget' => (float) $this->input->post('estimated_budget'),
            'created_at' => date('Y-m-d H:i:s')
        ));
        $this->session->set_flashdata('message', 'BOQ item saved successfully.');
        redirect('construction/boq');
    }

    public function work()
    {
        $data['products'] = $this->available_products();
        $data['issues'] = $this->db
            ->select('construction_material_issues.*, construction_projects.project_name, product_names.product_name')
            ->join('construction_projects', 'construction_projects.id = construction_material_issues.project_id', 'left')
            ->join('product_names', 'product_names.product_name_id = construction_material_issues.product_name_id', 'left')
            ->order_by('construction_material_issues.id', 'DESC')
            ->limit(25)
            ->get('construction_material_issues')
            ->result_array();
        $data['labours'] = $this->db
            ->select('construction_labours.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_labours.project_id', 'left')
            ->order_by('construction_labours.id', 'DESC')
            ->get('construction_labours')
            ->result_array();
        $data['attendance'] = $this->db
            ->select('construction_labour_attendance.*, construction_labours.labour_name, construction_projects.project_name')
            ->join('construction_labours', 'construction_labours.id = construction_labour_attendance.labour_id', 'left')
            ->join('construction_projects', 'construction_projects.id = construction_labour_attendance.project_id', 'left')
            ->order_by('construction_labour_attendance.id', 'DESC')
            ->limit(25)
            ->get('construction_labour_attendance')
            ->result_array();
        $data['payroll'] = $this->db
            ->select('construction_labour_payroll.*, construction_labours.labour_name, construction_projects.project_name')
            ->join('construction_labours', 'construction_labours.id = construction_labour_payroll.labour_id', 'left')
            ->join('construction_projects', 'construction_projects.id = construction_labour_payroll.project_id', 'left')
            ->order_by('construction_labour_payroll.id', 'DESC')
            ->limit(25)
            ->get('construction_labour_payroll')
            ->result_array();
        $data['categories'] = array('Diesel', 'Transportation', 'Food', 'Security', 'Accommodation', 'Miscellaneous');
        $data['expenses'] = $this->db
            ->select('construction_site_expenses.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_site_expenses.project_id', 'left')
            ->order_by('construction_site_expenses.id', 'DESC')
            ->limit(25)
            ->get('construction_site_expenses')
            ->result_array();
        $data['equipment'] = $this->db
            ->select('construction_equipment.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_equipment.project_id', 'left')
            ->order_by('construction_equipment.id', 'DESC')
            ->limit(25)
            ->get('construction_equipment')
            ->result_array();
        $data['progress'] = $this->db
            ->select('construction_progress.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_progress.project_id', 'left')
            ->order_by('construction_progress.progress_date', 'DESC')
            ->limit(25)
            ->get('construction_progress')
            ->result_array();
        $this->page('work', $data);
    }

    private function available_products()
    {
        return $this->db
            ->select('MIN(products.product_id) as product_id, products.product_name_id, products.campus_id, products.room_id, products.subroom_id, product_names.product_name, campuses.campus_name, rooms.room_name, subrooms.subroom_name, AVG(products.estimated_price) as estimated_price, COUNT(products.product_id) as stock_qty')
            ->from('products')
            ->join('product_names', 'product_names.product_name_id = products.product_name_id', 'left')
            ->join('campuses', 'campuses.campus_id = products.campus_id', 'left')
            ->join('rooms', 'rooms.room_id = products.room_id', 'left')
            ->join('subrooms', 'subrooms.subroom_id = products.subroom_id', 'left')
            ->where('products.status', 1)
            ->where('products.consume', 0)
            ->where('products.sold', 0)
            ->group_by('products.product_name_id')
            ->group_by('products.campus_id')
            ->group_by('products.room_id')
            ->group_by('products.subroom_id')
            ->order_by('product_names.product_name', 'ASC')
            ->get()
            ->result_array();
    }

    public function material_issue()
    {
        $data['products'] = $this->available_products();
        $data['issues'] = $this->db
            ->select('construction_material_issues.*, construction_projects.project_name, product_names.product_name')
            ->join('construction_projects', 'construction_projects.id = construction_material_issues.project_id', 'left')
            ->join('product_names', 'product_names.product_name_id = construction_material_issues.product_name_id', 'left')
            ->order_by('construction_material_issues.id', 'DESC')
            ->get('construction_material_issues')
            ->result_array();
        $this->page('material_issue', $data);
    }

    private function issue_material_to_project($projectId, $productId, $quantity, $issueDate, $remarks)
    {
        $product = $this->db->get_where('products', array('product_id' => $productId))->row_array();
        if ($projectId <= 0 || !$product || $quantity <= 0) {
            return array('success' => false, 'message' => 'Invalid project, product or quantity.');
        }

        $available = $this->db
            ->where('product_name_id', $product['product_name_id'])
            ->where('campus_id', $product['campus_id'])
            ->where('room_id', $product['room_id'])
            ->where('subroom_id', $product['subroom_id'])
            ->where('status', 1)
            ->where('consume', 0)
            ->where('sold', 0)
            ->count_all_results('products');

        if ($available < $quantity) {
            return array('success' => false, 'message' => 'Insufficient stock. Available quantity is ' . $available . '.');
        }

        $this->db->trans_start();
        for ($i = 0; $i < $quantity; $i++) {
            $item = $this->db
                ->where('product_name_id', $product['product_name_id'])
                ->where('campus_id', $product['campus_id'])
                ->where('room_id', $product['room_id'])
                ->where('subroom_id', $product['subroom_id'])
                ->where('status', 1)
                ->where('consume', 0)
                ->where('sold', 0)
                ->limit(1)
                ->get('products')
                ->row_array();

            $this->db->where('product_id', $item['product_id'])->update('products', array(
                'consume' => 1,
                'consume_date' => $issueDate,
                'consume_reason' => 'Issued to construction project #' . $projectId
            ));
        }

        $unitCost = (float) $product['estimated_price'];
        $this->db->insert('construction_material_issues', array(
            'project_id' => $projectId,
            'product_id' => $productId,
            'product_name_id' => $product['product_name_id'],
            'campus_id' => $product['campus_id'],
            'room_id' => $product['room_id'],
            'subroom_id' => $product['subroom_id'],
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $unitCost * $quantity,
            'issue_date' => $issueDate,
            'issued_by' => $this->session->userdata('user_id'),
            'remarks' => $remarks,
            'created_at' => date('Y-m-d H:i:s')
        ));
        $this->db->trans_complete();

        return array(
            'success' => $this->db->trans_status(),
            'message' => $this->db->trans_status() ? 'Material issued successfully.' : 'Material issue failed.'
        );
    }

    public function save_material_issue()
    {
        $result = $this->issue_material_to_project(
            (int) $this->input->post('project_id'),
            (int) $this->input->post('product_id'),
            (int) $this->input->post('quantity'),
            $this->input->post('issue_date') ?: date('Y-m-d'),
            $this->input->post('remarks')
        );
        $this->session->set_flashdata($result['success'] ? 'message' : 'error', $result['message']);
        redirect('construction/work');
    }

    public function entries()
    {
        $data['products'] = $this->available_products();
        $data['entries'] = $this->recent_cost_entries(100);
        $this->page('entries', $data);
    }

    public function save_entry()
    {
        $type = $this->input->post('entry_type');
        $projectId = (int) $this->input->post('project_id');
        $entryDate = $this->input->post('entry_date') ?: date('Y-m-d');
        $description = $this->input->post('description');

        if ($type === 'Material') {
            $result = $this->issue_material_to_project(
                $projectId,
                (int) $this->input->post('product_id'),
                (int) $this->input->post('quantity'),
                $entryDate,
                $description
            );
            $this->session->set_flashdata($result['success'] ? 'message' : 'error', $result['message']);
            redirect('construction/entries');
            return;
        }

        $allowed = array('Labour', 'Site Expense', 'Equipment');
        $amount = (float) $this->input->post('amount');
        if ($projectId <= 0 || !in_array($type, $allowed) || $amount <= 0) {
            $this->session->set_flashdata('error', 'Please select project, cost type and valid amount.');
            redirect('construction/entries');
            return;
        }

        $this->db->insert('construction_cost_entries', array(
            'project_id' => $projectId,
            'entry_type' => $type,
            'entry_date' => $entryDate,
            'amount' => $amount,
            'description' => $description,
            'created_by' => $this->session->userdata('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ));
        $this->session->set_flashdata('message', 'Cost entry saved successfully.');
        redirect('construction/entries');
    }

    public function labour()
    {
        $data['labours'] = $this->db
            ->select('construction_labours.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_labours.project_id', 'left')
            ->order_by('construction_labours.id', 'DESC')
            ->get('construction_labours')
            ->result_array();
        $data['attendance'] = $this->db
            ->select('construction_labour_attendance.*, construction_labours.labour_name, construction_projects.project_name')
            ->join('construction_labours', 'construction_labours.id = construction_labour_attendance.labour_id', 'left')
            ->join('construction_projects', 'construction_projects.id = construction_labour_attendance.project_id', 'left')
            ->order_by('construction_labour_attendance.id', 'DESC')
            ->limit(25)
            ->get('construction_labour_attendance')
            ->result_array();
        $data['payroll'] = $this->db
            ->select('construction_labour_payroll.*, construction_labours.labour_name, construction_projects.project_name')
            ->join('construction_labours', 'construction_labours.id = construction_labour_payroll.labour_id', 'left')
            ->join('construction_projects', 'construction_projects.id = construction_labour_payroll.project_id', 'left')
            ->order_by('construction_labour_payroll.id', 'DESC')
            ->limit(25)
            ->get('construction_labour_payroll')
            ->result_array();
        $this->page('labour', $data);
    }

    public function save_labour()
    {
        $this->db->insert('construction_labours', array(
            'project_id' => (int) $this->input->post('project_id'),
            'labour_name' => $this->input->post('labour_name'),
            'cnic' => $this->input->post('cnic'),
            'mobile' => $this->input->post('mobile'),
            'designation' => $this->input->post('designation'),
            'daily_wage' => (float) $this->input->post('daily_wage'),
            'created_at' => date('Y-m-d H:i:s')
        ));
        $this->session->set_flashdata('message', 'Labour saved successfully.');
        redirect('construction/work');
    }

    public function save_labour_attendance()
    {
        $labour = $this->db->get_where('construction_labours', array('id' => (int) $this->input->post('labour_id')))->row_array();
        $this->db->insert('construction_labour_attendance', array(
            'labour_id' => (int) $this->input->post('labour_id'),
            'project_id' => (int) $this->input->post('project_id'),
            'attendance_date' => $this->input->post('attendance_date'),
            'status' => $this->input->post('status'),
            'overtime_hours' => (float) $this->input->post('overtime_hours'),
            'overtime_amount' => (float) $this->input->post('overtime_amount'),
            'created_at' => date('Y-m-d H:i:s')
        ));
        if ($this->input->post('status') == 'Present' && $labour) {
            $this->db->insert('construction_labour_payroll', array(
                'labour_id' => (int) $this->input->post('labour_id'),
                'project_id' => (int) $this->input->post('project_id'),
                'payroll_month' => date('M-Y', strtotime($this->input->post('attendance_date'))),
                'payable_amount' => (float) $labour['daily_wage'] + (float) $this->input->post('overtime_amount'),
                'paid_amount' => 0,
                'remarks' => 'Auto payroll from attendance',
                'created_at' => date('Y-m-d H:i:s')
            ));
        }
        $this->session->set_flashdata('message', 'Labour attendance saved successfully.');
        redirect('construction/work');
    }

    public function save_labour_advance()
    {
        $this->db->insert('construction_labour_advances', array(
            'labour_id' => (int) $this->input->post('labour_id'),
            'project_id' => (int) $this->input->post('project_id'),
            'advance_date' => $this->input->post('advance_date'),
            'amount' => (float) $this->input->post('amount'),
            'remarks' => $this->input->post('remarks'),
            'created_at' => date('Y-m-d H:i:s')
        ));
        $this->session->set_flashdata('message', 'Labour advance saved successfully.');
        redirect('construction/work');
    }

    public function contractors()
    {
        $data['contractors'] = $this->contractor_summary();
        $data['payments'] = $this->db
            ->select('construction_contractor_payments.*, construction_contractors.contractor_name, construction_projects.project_name')
            ->join('construction_contractors', 'construction_contractors.id = construction_contractor_payments.contractor_id', 'left')
            ->join('construction_projects', 'construction_projects.id = construction_contractor_payments.project_id', 'left')
            ->order_by('construction_contractor_payments.id', 'DESC')
            ->limit(25)
            ->get('construction_contractor_payments')
            ->result_array();
        $this->page('contractors', $data);
    }

    public function save_contractor()
    {
        $this->db->insert('construction_contractors', array(
            'project_id' => (int) $this->input->post('project_id'),
            'contractor_name' => $this->input->post('contractor_name'),
            'contact_details' => $this->input->post('contact_details'),
            'contract_amount' => (float) $this->input->post('contract_amount'),
            'advance_payment' => (float) $this->input->post('advance_payment'),
            'running_bills' => (float) $this->input->post('running_bills'),
            'final_bill' => (float) $this->input->post('final_bill'),
            'created_at' => date('Y-m-d H:i:s')
        ));
        $this->session->set_flashdata('message', 'Contractor saved successfully.');
        redirect('construction/contractors');
    }

    public function save_contractor_payment()
    {
        $contractor = $this->db->get_where('construction_contractors', array('id' => (int) $this->input->post('contractor_id')))->row_array();
        $this->db->insert('construction_contractor_payments', array(
            'contractor_id' => (int) $this->input->post('contractor_id'),
            'project_id' => (int) @$contractor['project_id'],
            'payment_date' => $this->input->post('payment_date'),
            'amount' => (float) $this->input->post('amount'),
            'payment_type' => $this->input->post('payment_type'),
            'remarks' => $this->input->post('remarks'),
            'created_at' => date('Y-m-d H:i:s')
        ));
        $this->session->set_flashdata('message', 'Contractor payment saved successfully.');
        redirect('construction/contractors');
    }

    public function expenses()
    {
        $data['categories'] = array('Diesel', 'Transportation', 'Food', 'Security', 'Accommodation', 'Miscellaneous');
        $data['expenses'] = $this->db
            ->select('construction_site_expenses.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_site_expenses.project_id', 'left')
            ->order_by('construction_site_expenses.id', 'DESC')
            ->get('construction_site_expenses')
            ->result_array();
        $this->page('expenses', $data);
    }

    public function save_expense()
    {
        $attachment = '';
        if (!empty($_FILES['attachment']['name'])) {
            $config['upload_path'] = 'uploads/';
            $config['allowed_types'] = '*';
            $config['encrypt_name'] = TRUE;
            $this->upload->initialize($config);
            if ($this->upload->do_upload('attachment')) {
                $attachment = $this->upload->data('file_name');
            }
        }
        $this->db->insert('construction_site_expenses', array(
            'project_id' => (int) $this->input->post('project_id'),
            'category' => $this->input->post('category'),
            'expense_date' => $this->input->post('expense_date'),
            'amount' => (float) $this->input->post('amount'),
            'description' => $this->input->post('description'),
            'attachment' => $attachment,
            'created_by' => $this->session->userdata('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ));
        $this->session->set_flashdata('message', 'Site expense saved successfully.');
        redirect('construction/work');
    }

    public function equipment()
    {
        $data['equipment'] = $this->db
            ->select('construction_equipment.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_equipment.project_id', 'left')
            ->order_by('construction_equipment.id', 'DESC')
            ->get('construction_equipment')
            ->result_array();
        $this->page('equipment', $data);
    }

    public function save_equipment()
    {
        $this->db->insert('construction_equipment', array(
            'project_id' => (int) $this->input->post('project_id'),
            'equipment_name' => $this->input->post('equipment_name'),
            'operator' => $this->input->post('operator'),
            'fuel_consumption' => (float) $this->input->post('fuel_consumption'),
            'maintenance_cost' => (float) $this->input->post('maintenance_cost'),
            'repair_cost' => (float) $this->input->post('repair_cost'),
            'usage_history' => $this->input->post('usage_history'),
            'created_at' => date('Y-m-d H:i:s')
        ));
        $this->session->set_flashdata('message', 'Equipment saved successfully.');
        redirect('construction/work');
    }

    public function progress()
    {
        $data['progress'] = $this->db
            ->select('construction_progress.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_progress.project_id', 'left')
            ->order_by('construction_progress.progress_date', 'DESC')
            ->get('construction_progress')
            ->result_array();
        $this->page('progress', $data);
    }

    public function save_progress()
    {
        $this->db->insert('construction_progress', array(
            'project_id' => (int) $this->input->post('project_id'),
            'milestone' => $this->input->post('milestone'),
            'foundation_percent' => (float) $this->input->post('foundation_percent'),
            'structure_percent' => (float) $this->input->post('structure_percent'),
            'finishing_percent' => (float) $this->input->post('finishing_percent'),
            'overall_percent' => (float) $this->input->post('overall_percent'),
            'remarks' => $this->input->post('remarks'),
            'progress_date' => $this->input->post('progress_date'),
            'created_at' => date('Y-m-d H:i:s')
        ));
        $this->session->set_flashdata('message', 'Progress saved successfully.');
        redirect('construction/work');
    }

    public function reports()
    {
        $data['project_costs'] = $this->project_cost_rows();
        $data['contractors'] = $this->contractor_summary();
        $data['labour'] = $this->db
            ->select('construction_labour_payroll.*, construction_labours.labour_name, construction_projects.project_name')
            ->join('construction_labours', 'construction_labours.id = construction_labour_payroll.labour_id', 'left')
            ->join('construction_projects', 'construction_projects.id = construction_labour_payroll.project_id', 'left')
            ->order_by('construction_labour_payroll.id', 'DESC')
            ->get('construction_labour_payroll')
            ->result_array();
        $data['expenses'] = $this->db
            ->select('construction_site_expenses.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_site_expenses.project_id', 'left')
            ->order_by('construction_site_expenses.id', 'DESC')
            ->get('construction_site_expenses')
            ->result_array();
        $data['equipment'] = $this->db
            ->select('construction_equipment.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_equipment.project_id', 'left')
            ->order_by('construction_equipment.id', 'DESC')
            ->get('construction_equipment')
            ->result_array();
        $data['progress'] = $this->db
            ->select('construction_progress.*, construction_projects.project_name')
            ->join('construction_projects', 'construction_projects.id = construction_progress.project_id', 'left')
            ->order_by('construction_progress.progress_date', 'DESC')
            ->get('construction_progress')
            ->result_array();
        $data['material'] = $this->db
            ->select('construction_material_issues.*, construction_projects.project_name, product_names.product_name')
            ->join('construction_projects', 'construction_projects.id = construction_material_issues.project_id', 'left')
            ->join('product_names', 'product_names.product_name_id = construction_material_issues.product_name_id', 'left')
            ->order_by('construction_material_issues.id', 'DESC')
            ->get('construction_material_issues')
            ->result_array();
        $this->page('reports', $data);
    }

    private function project_cost_rows()
    {
        $projects = $this->db->get('construction_projects')->result_array();
        foreach ($projects as $key => $project) {
            $projectId = $project['id'];
            $material = (float) $this->db->select_sum('total_cost')->where('project_id', $projectId)->get('construction_material_issues')->row()->total_cost;
            $labour = (float) $this->db->select_sum('paid_amount')->where('project_id', $projectId)->get('construction_labour_payroll')->row()->paid_amount + $this->sum_simple_entries('Labour', $projectId);
            $contractor = (float) $this->db->select_sum('amount')->where('project_id', $projectId)->get('construction_contractor_payments')->row()->amount + $this->sum_simple_entries('Contractor', $projectId);
            $expense = (float) $this->db->select_sum('amount')->where('project_id', $projectId)->get('construction_site_expenses')->row()->amount + $this->sum_simple_entries('Site Expense', $projectId);
            $equipment = (float) $this->db->select('SUM(maintenance_cost + repair_cost) as total')->where('project_id', $projectId)->get('construction_equipment')->row()->total + $this->sum_simple_entries('Equipment', $projectId);
            $actual = $material + $labour + $contractor + $expense + $equipment;
            $projects[$key]['material_cost'] = $material;
            $projects[$key]['labour_cost'] = $labour;
            $projects[$key]['contractor_cost'] = $contractor;
            $projects[$key]['site_expense'] = $expense;
            $projects[$key]['equipment_cost'] = $equipment;
            $projects[$key]['actual_cost'] = $actual;
            $projects[$key]['remaining_budget'] = (float) $project['budget'] - $actual;
        }
        return $projects;
    }
}
