<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Reports extends CI_Controller
{
    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     *        http://example.com/index.php/welcome
     *    - or -
     *        http://example.com/index.php/welcome/index
     *    - or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('clas');	
        $this->ensure_discount_report_columns();
    }

    private function ensure_discount_report_columns()
    {
        if ($this->db->table_exists('discounts_approval')) {
            if (!$this->db->field_exists('approved_by', 'discounts_approval')) {
                $this->db->query("ALTER TABLE discounts_approval ADD approved_by VARCHAR(255) NULL AFTER created_by");
            }
            if (!$this->db->field_exists('approved_at', 'discounts_approval')) {
                $this->db->query("ALTER TABLE discounts_approval ADD approved_at DATETIME NULL AFTER approved_by");
            }
        }

        foreach (array('access_rules', 'access') as $table) {
            if ($this->db->table_exists($table) && !$this->db->field_exists('reports_discount_report', $table)) {
                $this->db->query("ALTER TABLE `$table` ADD `reports_discount_report` TINYINT(1) NULL DEFAULT NULL");
            }
        }
    }

    private function can_access_discount_report()
    {
        if ($this->session->userdata('role') == 'Admin') {
            return true;
        }

        $access = checkUserAccess();
        return !empty($access) && isset($access[0]['reports_discount_report']) && (int) $access[0]['reports_discount_report'] === 1;
    }

    public function discount_report()
    {
        if (!$this->can_access_discount_report()) {
            $this->session->set_flashdata('error', 'You do not have access to this report.');
            redirect('dashboard');
            return;
        }

        $fromDate = $this->input->post('from_date') ?: date('Y-m-01');
        $toDate = $this->input->post('to_date') ?: date('Y-m-d');

        $access = checkUserAccess();
        $campusIds = @explode(',', $access[0]['campus_ids']);

        $this->db->select('
            discounts_approval.*,
            students.first_name,
            students.last_name,
            students.roll_no,
            students.cnic,
            classes.name as class_name,
            courses.course_name,
            campuses.campus_name
        ');
        $this->db->from('discounts_approval');
        $this->db->join('students', 'students.student_id = discounts_approval.student_id', 'left');
        $this->db->join('classes', 'classes.class_id = students.class_id', 'left');
        $this->db->join('courses', 'courses.course_id = classes.course_id', 'left');
        $this->db->join('campuses', 'campuses.campus_id = classes.campus_id', 'left');
        $this->db->where('discounts_approval.status', 1);
        $this->db->where('DATE(discounts_approval.created_at) >=', $fromDate);
        $this->db->where('DATE(discounts_approval.created_at) <=', $toDate);

        if ($this->session->userdata('role') != 'Admin' && !empty($campusIds)) {
            $this->db->where_in('campuses.campus_id', $campusIds);
        }

        $this->db->order_by('discounts_approval.created_at', 'DESC');
        $data['discounts'] = $this->db->get()->result_array();
        $data['from_date'] = $fromDate;
        $data['to_date'] = $toDate;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/discount_report', $data);
        $this->load->view('inc/footer');
    }

    public function students_fee_problem()
    {
        $access = checkUserAccess();
        $campus_ids = @explode(',', $access[0]['campus_ids']);
        if ($this->session->userdata('role') != 'Admin') {
            $this->db->where_in('campus_id', $campus_ids);
        }
        $this->db->where('campuses.status', 1);
        $data['campuses'] = $this->db->get('campuses')->result_array();

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/students_fee_problem', $data);
        $this->load->view('inc/footer');
    }

    public function teacher_questions()
    {
        $data['my_course'] = "";
        $data['my_subject'] = "";

        if ($this->input->post('from_date') == NULL) {
            $from_date = date("Y-m-d");
            $to_date = date("Y-m-d");;
        } else {

            $from_date = $this->input->post('from_date');
            $to_date = $this->input->post('to_date');
            $data['my_course'] = $this->db->get_where('courses', 'course_id = "' . $this->input->post('course_id') . '"')->row();
            $data['my_subject'] = $this->db->get_where('course_subjects', 'course_subject_id  = "' . $this->input->post('subject_id') . '"')->row();
            $data['my_teacher'] = $this->db->get_where('users', 'user_id  = "' . $this->input->post('teacher_id') . '"')->row();
            $data['my_topics'] = $this->db->get_where('topics', 'course_subject_id  = "' . $this->input->post('subject_id') . '"')->result_array();
            $data['my_topics'] = array_column($data['my_topics'], "topic_id");
        }

        $data['courses'] = $this->db->get_where('courses', 'status = 1')->result_array();
        $data['users'] = $this->db->join('designations', 'designations.designation_id = users.designation_id')
            ->join('departments', 'departments.department_id = users.department_id')->get_where('users', 'users.status = 1 and departments.department_id = 13')->result_array();

        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $data['dates'] = $this->getDatesFromRange($from_date, $to_date, "Y-m-d");


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/teacher_questions', $data);
        $this->load->view('inc/footer');
    }

    public function campus_fee_problem($campus_id)
    {
        $this->db->select('students.*,classes.name as class_name,campuses.campus_name,courses.course_name');
        $this->db->from('students');
        $this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
        $this->db->join('courses', 'classes.course_id=courses.course_id', 'inner');
        $this->db->join('campuses', 'campuses.campus_id=classes.campus_id', 'inner');
        $this->db->where(array('campuses.campus_id' => $campus_id, 'students.status' => 1, 'students.contract_id' => 0));
        $students = $this->db->get()->result_array();

        $stud = array();
        foreach ($students as $student) {
            $this->db->select_sum('amount');
            $this->db->from('payments');
            $this->db->where(array('student_id' => $student['student_id'], 'payment_plan!=' => 'consulation fee'));
            $payment = $this->db->get()->result_array();

            if ($payment[0]['amount'] < $student['total_fee']) {
                array_push($stud, $student);
            }
            if (count($payment) < 1) {
                array_push($stud, $student);
            }
        }
        $data['students'] = $stud;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/campus_fee_problem', $data);
        $this->load->view('inc/footer');
    }

    function getDatesFromRange($start, $end, $format = 'Y-m-d')
    {
        $array = array();
        $interval = new DateInterval('P1D');

        $realEnd = new DateTime($end);
        $realEnd->add($interval);

        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

        foreach ($period as $date) {
            $array[] = $date->format($format);
        }

        return $array;
    }

    public function sms_devices_data()
    {

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/sms_devices_data');
        $this->load->view('inc/footer');
    }

    public function smsReportTable()
    {
        $sms_gateways = $this->db->join('campuses', 'campuses.campus_id = sms_gateway.campus_id')->get_where('sms_gateway', 'sms_gateway.status = "active"')->result_array();

        if (!empty($sms_gateways)) {
            foreach ($sms_gateways as $key => $purchase) {
                $nestedData['id'] = ($key);
                $nestedData['campus_name'] = $purchase['campus_name'];
                $nestedData['device_id'] = $purchase['device_id'];
                $nestedData['percentage'] = $purchase['percentage'];
                $nestedData['last_sent'] = $purchase['updated_at'];
                $nestedData['sms_count'] = $this->db->select('count(*) as sms_count')->get_where("sms", "sms.sent_from = '" . $purchase['device_id'] . "' 
                and date >= '" . date("Y-m-d") . " 00:00:00' and date <= '" . date("Y-m-d") . " 23:59:59'")->row()->sms_count;

                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw" => intval($this->input->post('draw')),
            "recordsTotal" => intval(count($sms_gateways)),
            "recordsFiltered" => intval(count($sms_gateways)),
            "data" => $data
        );
        echo json_encode($json_data);
    }

    public function PettyCashReport()
    {
        if (@$this->input->post("to_date"))
            $date = $this->input->post("to_date");
        else
            $date = date("Y-m-d");

        $this->db->select('*');
        $this->db->from('petty_cash_college_wise');
        $this->db->join('campuses', 'campuses.campus_id = petty_cash_college_wise.campus_id', 'left');
        $this->db->join('users', 'users.user_id = petty_cash_college_wise.assign_to', 'left');
        $this->db->join('designations', 'designations.designation_id = users.designation_id', 'left');
        $this->db->where('petty_cash_college_wise.petty_status', "1");
        $data['Pettycashs'] = $this->db->get()->result_array();

        foreach ($data['Pettycashs'] as $index => $petts) {
            $data['Pettycashs'][$index]['opening_balance'] = $this->get_opening_balance($petts ['id'], $date);
            $data['Pettycashs'][$index]['remaining_balance'] = pettycash_statement($petts ['id']);
            $data['Pettycashs'][$index]['expenses'] = $this->get_expenses($petts ['id'], $date);
            $data['Pettycashs'][$index]['reversal'] = $this->get_expenses_reversals($petts ['id'], $date);
            $data['Pettycashs'][$index]['received'] = $this->get_received($petts ['id'], $date);
            $data['Pettycashs'][$index]['sent'] = $this->get_sent($petts ['id'], $date);
        }
        $data['selected_date'] = $date;
        $today = $date;

        // Get Campus Closings
        $this->db->select('*,closing_persons.campus_id as campus_id');
        $this->db->from('closing_persons');
        $this->db->join('campuses','campuses.campus_id = closing_persons.campus_id','left');
        $this->db->join('users','users.user_id = closing_persons.user_id','left');
        $this->db->where('closing_persons.active_status = "1"');
        $dataclose = $this->db->get()->result_array();

        $sq = 'select closing_perday.campus_id,campus_name,
                (select for_day from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
		        (select for_month from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,
		        MAX(for_year) as year from closing_perday 
		        left join campuses on campuses.campus_id = closing_perday.campus_id 
		        where (select count(*) from closing_persons where closing_persons.campus_id = closing_perday.campus_id and closing_persons.active_status = 1) > 0
		        GROUP by closing_perday.campus_id';
        $data['campusclosings'] = $this->db->query($sq)->result_array();

        $sq = 'select closing_perday.campus_id,campus_name,
		(select for_day   from closing_perday where campus_id = campuses.campus_id and checked_by = "1" order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
		(select for_month from closing_perday where campus_id = campuses.campus_id and checked_by = "1" order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,
		MAX(for_year) as year from closing_perday 
		left join campuses on campuses.campus_id = closing_perday.campus_id
		where (select count(*) from closing_persons where closing_persons.campus_id = closing_perday.campus_id and closing_persons.active_status = 1) > 0
        GROUP by closing_perday.campus_id';
        $data['campusclosingverified'] = $this->db->query($sq)->result_array();
        $data['campusclosings'] = array_values($data['campusclosings']);

        foreach ($dataclose as $key=>$closing) {
            $sq = "select * from closing_perday where campus_id = '".$closing['campus_id']."' and for_month = '".date('m', strtotime($today))."' and for_day = '".date('d', strtotime($today))."'and for_year = '".date('Y', strtotime($today))."'";
            $closed = $this->db->query($sq)->result_array();

            if (count($closed)>0) {
                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('closing_id = "'.$closed[0]['campus_closing_id'].'"');
                $query = $this->db->get()->result_array();

                $value = array_sum(array_column($query,'actual_amount'));

                $dataclose[$key]['closing_amount'] = $value;
                $dataclose[$key]['closed_status'] = '1';
                $dataclose[$key]['closing_id'] = $closed[0]['id'];
                $dataclose[$key]['transaction_no'] = $closed[0]['transaction_no'];
                $dataclose[$key]['close_type'] = $closed[0]['close_type'];
                $dataclose[$key]['closed_by'] = $closed[0]['closed_by'];
                $dataclose[$key]['checked_by'] = $closed[0]['checked_by'];
                $dataclose[$key]['account_id'] = $closed[0]['account_id'];
                $dataclose[$key]['partialy_closed_image'] = $closed[0]['partialy_closed_image'];
            }
            else {
                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                $this->db->where('fee_pay_through = "college"');
                $this->db->where('actual_paid_date = "'.$today.'"');
                $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
                $query = $this->db->get()->result_array();

                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                $this->db->where('merged_challan is null');
                $this->db->where('fee_pay_through = "college"');
                $this->db->where('actual_paid_date = "'.$today.'"');
                $this->db->where('payments.paid = 1');
                $query2 = $this->db->get()->result_array();

                $yesterday = date('Y-m-d', strtotime($today. ' - 1 days'));

                $sq = "select * from closing_perday where campus_id = '".$closing['campus_id']."' and for_month = '".date('m', strtotime($yesterday))."' and for_day = '".date('d', strtotime($yesterday))."'and for_year = '".date('Y', strtotime($yesterday))."'";
                $closed = $this->db->query($sq)->result_array();

                if (count($closed)>0)  {

                    $this->db->select('*');
                    $this->db->from('payments');
                    $this->db->join('students','students.student_id = payments.student_id','left');
                    $this->db->join('courses','courses.course_id=students.course_id','inner');
                    $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                    $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                    $this->db->where('fee_pay_through = "college"');
                    $this->db->where('actual_paid_date = "'.$yesterday.'"');
                    $this->db->where('closing_id IS NULL');
                    $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
                    $query3 = $this->db->get()->result_array();

                    $this->db->select('*');
                    $this->db->from('payments');
                    $this->db->join('students','students.student_id = payments.student_id','left');
                    $this->db->join('courses','courses.course_id=students.course_id','inner');
                    $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                    $this->db->where('merged_challan is null');
                    $this->db->where('fee_pay_through = "college"');
                    $this->db->where('actual_paid_date = "'.$yesterday.'"');
                    $this->db->where('closing_id IS NULL');
                    $this->db->where('payments.paid = 1');
                    $query4 = $this->db->get()->result_array();
                    $final = array_merge($query3, $query4,$query, $query2);
                }

                else {
                    $final = array_merge($query, $query2);
                }

                $value = array_sum(array_column($final,'actual_amount'));

                $this->db->select('sum(asset_sales.sale_amount) as total');
                $this->db->from('asset_sales');
                $this->db->join('products','products.product_id = asset_sales.product_id','inner');
                $this->db->where("asset_sales.sold_date >= '$today 00:00:00' and asset_sales.sold_date <= '$today 23:59:59' and products.campus_id = '".$closing['campus_id']."'");
                $asset_sales_sum_today = $this->db->get()->result_array();

                $this->db->select('sum(asset_sales.sale_amount) as total');
                $this->db->from('asset_sales');
                $this->db->join('products','products.product_id = asset_sales.product_id','inner');
                $this->db->where("asset_sales.sold_date >= '$yesterday 00:00:00' and asset_sales.sold_date <= '$yesterday 23:59:59' and products.campus_id = '".$closing['campus_id']."' and closing_id IS NULL");
                $asset_sales_sum_yesterday = $this->db->get()->result_array();

                $asset_sale_amount = $asset_sales_sum_today[0]['total'] + $asset_sales_sum_yesterday[0]['total'];

                $this->db->select('sum(sales_payments.payment_amount) as total');
                $this->db->from('sales');
                $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
                $this->db->join('people','people.person_id  = sales.customer_id','inner');
                $this->db->where("sales.sale_time >= '$today 00:00:00' and sales.sale_time <= '$today 23:59:59' and sales.campus_id = '".$closing['campus_id']."'");
                $sales_sum = $this->db->get()->result_array();

                $this->db->select('sum(sales_payments.payment_amount) as total');
                $this->db->from('sales');
                $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
                $this->db->join('people','people.person_id  = sales.customer_id','inner');
                $this->db->where("sales.sale_time >= '$yesterday 00:00:00' and sales.sale_time <= '$yesterday 23:59:59' and sales.campus_id = '".$closing['campus_id']."'");
                $sales_sum_yesterday = $this->db->get()->result_array();

                $sale_amount = $sales_sum[0]['total'] + $sales_sum_yesterday[0]['total'];

                $dataclose[$key]['closing_amount'] = $value+$sale_amount+$asset_sale_amount;
                $dataclose[$key]['closed_status'] = '0';
                $dataclose[$key]['closing_id'] = '';
                $dataclose[$key]['close_type'] = '0';
                $dataclose[$key]['transaction_no'] = '';
                $dataclose[$key]['closed_by'] = '';
                $dataclose[$key]['checked_by'] = '';
                $dataclose[$key]['account_id'] = '';
                $dataclose[$key]['partialy_closed_image'] = @$closed[0]['partialy_closed_image'];
            }
        }
        $data['closings']=$dataclose;
        ///////////   End Closing /////////////

        $this->db->select('*');
        $this->db->from('accounts');
        $this->db->where('type',"0");
        $data['accounts'] = $this->db->get()->result_array();

        foreach ($data['accounts'] as $index => $petts) {
            $data['accounts'][$index]['opening_balance'] = $this->get_accounts_opening_balance($petts ['id'], $date);
            $data['accounts'][$index]['remaining_balance'] = accountCash_balance($petts ['id']);
            $data['accounts'][$index]['received'] = $this->get_accounts_received($petts ['id'], $date);
            $data['accounts'][$index]['sent'] = $this->get_accounts_sent($petts ['id'], $date);
        }

        $campuses = $this->db->select("*,0 as admissions")->get_where("campuses","status = '1' and roll_no_code IS NOT NULL")->result_array();
        $courses  = $this->db->select("course_id,course_name,course_code,0 as admissions_count")->get_where("courses","status = '1'")->result_array();

        foreach ($campuses as $main_key=>$campus) {

            $campuses[$main_key]['courses'] = $courses;
            $this->db->select('payments.student_id,students.course_id');
            $this->db->from('payments');
            $this->db->join('students','students.student_id = payments.student_id');
            $this->db->join('classes','classes.class_id = students.class_id');
            $this->db->where('payments.actual_paid_date', $today);
            $this->db->where('payments.paid', '1');
            $this->db->where('classes.campus_id', $campus['campus_id']);
            $this->db->group_by('payments.student_id');
            $payments = $this->db->get()->result_array();

            foreach ($payments as $key => $payment) {
                $cos = $this->db->select('*')->get_where("payments", "student_id = '" . $payment['student_id'] . "' and paid = 1")->result_array();
                if (count($cos) > 1){
                    unset($payments[$key]);
                }else{
                    foreach ($campuses[$main_key]['courses'] as $is=>$csrs) {
                        if ($payment['course_id'] == $csrs['course_id']){
                            $campuses[$main_key]['courses'][$is]['admissions_count']++;
                        }
                    }
                }
            }
            $campuses[$main_key]['admissions_count'] = count($payments);
        }

        $data['admissions'] = $campuses;
        $data['courses'] = $courses;

        $this->db->select('*');
        $this->db->from('accounts');
        $this->db->where('type',"1");
        $this->db->where('for_closing',"1");
        $data['bank_accounts'] = $this->db->get()->result_array();

        foreach ($data['bank_accounts'] as $index => $petts) {
            $dat_status = $this->get_bank_accounts_status($petts ['id'], $date);
            $tag_untag = $this->get_bank_accounts_tagged($petts ['id'], $date);
            $data['bank_accounts'][$index]['status'] = $dat_status;
            $data['bank_accounts'][$index]['tagged'] = $dat_status != -1? $tag_untag['counted'] : "Missing Statement";
            $data['bank_accounts'][$index]['untagged'] = $dat_status != -1? $tag_untag['uncounted'] : "Missing Statement";
            $data['bank_accounts'][$index]['expenses'] = $this->get_bank_account_expense($petts ['id'], $date);
        }

        $data['settlements'] = $this->db
            ->join("students","students.student_id = students_payments.student_id","left")
            ->join("classes","classes.class_id = students.class_id","left")
            ->join("courses","courses.course_id = classes.course_id","left")
            ->join("campuses","campuses.campus_id = classes.campus_id","left")
            ->get_where("students_payments","students_payments.transaction_status = 'PAID' and students_payments.settlement_id IS NULL and students_payments.created_on <= '$date'")
            ->result_array();

        $this->db->select('*');
        $this->db->from('discounts_approval');
        $this->db->join('students','students.student_id = discounts_approval.student_id');
        $this->db->join('courses','courses.course_id = students.course_id');
        $this->db->join('classes','classes.class_id = students.class_id');
        $this->db->join('campuses','campuses.campus_id = classes.campus_id');
        $this->db->where('discounts_approval.status',"1");
        $this->db->where("discounts_approval.created_at >= '$date 00:00:00' and discounts_approval.created_at <= '$date 23:59:59'");
        $data['discounts'] = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('struckofdetails_students');
        $this->db->join('students','students.student_id = struckofdetails_students.student_id');
        $this->db->join('courses','courses.course_id = students.course_id');
        $this->db->join('classes','classes.class_id = students.class_id');
        $this->db->join('campuses','campuses.campus_id = classes.campus_id');
        $this->db->where('struckofdetails_students.status',"1");
        $this->db->where("struckofdetails_students.created_at >= '$date 00:00:00' and struckofdetails_students.created_at <= '$date 23:59:59'");
        $this->db->order_by("struckofdetails_students.id","DESC");
        $this->db->group_by("struckofdetails_students.student_id");
        $data['struck_of'] = $this->db->get()->result_array();

        $this->db->select('*');
        $this->db->from('payments');
        $this->db->join('students','students.student_id = payments.student_id');
        $this->db->join('courses','courses.course_id = students.course_id');
        $this->db->join('classes','classes.class_id = students.class_id');
        $this->db->join('campuses','campuses.campus_id = classes.campus_id');
        $this->db->where('payments.paid',"1");
        $this->db->where("payments.actual_paid_date = '$date' and payments.payment_comment = 'Re-Admission Fee'");
        $data['revive_of'] = $this->db->get()->result_array();

        $this->db->select('count(*) as total');
        $this->db->from('update_student_requests');
        $this->db->where("update_date >= '$date 00:00:00' and update_date <= '$date 23:59:59'");
        $data['student_requests'] = $this->db->get()->row();

        //$this->db->select('count(*) as total');
        //$this->db->from('update_payment_requests');
        //$this->db->where("update_date >= '$date 00:00:00' and update_date <= '$date 23:59:59'");
        //$data['fee_requests'] = $this->db->get()->row();
        //FEE REQUESTS
        //$this->db->select('*');
        //$this->db->from('update_payment_requests');
        //$this->db->where("update_date >= '$date 00:00:00' and update_date <= '$date 23:59:59'");
        //$data['fee_requests'] = $this->db->get()->result_array();
        $this->db->select('update_payment_requests.*, students.first_name, students.last_name, students.roll_no');
		$this->db->from('update_payment_requests');
		$this->db->join('students', 'students.student_id=update_payment_requests.student_id', 'inner');
		$this->db->join('classes', 'classes.class_id=students.class_id', 'inner');
		$this->db->join('campuses', 'classes.campus_id=campuses.campus_id', 'inner');
        $this->db->where(array('update_payment_requests.update_date >='=>$date.' 00:00:00','update_payment_requests.update_date <='=>$date.' 23:59:59','update_payment_requests.ok_by_admin'=>1));
		$data['fee_requests'] = $this->db->get()->result_array();

        $this->db->select('campuses.*');
        $this->db->from('users');
        $this->db->join('campuses','campuses.campus_id = users.campus_id');
        $this->db->where('users.status',"1");
        $this->db->group_by('users.campus_id');
        $data['campuses_attendance'] = $this->db->get()->result_array();

        foreach ($data['campuses_attendance'] as $key=>$cam)
        {
            $das_data = $this->get_campus_status($cam['campus_id'],$date);
            $data['campuses_attendance'][$key]['present'] = $das_data['counted'];
            $data['campuses_attendance'][$key]['absents'] = $das_data['uncounted'];
        }
        $data['last_closing'] = $this->db->order_by('id','DESC')->get("accounts_daily_closing")->row()->closing_date;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/pettycash_accounts_closing', $data);
        $this->load->view('inc/footer');
    }

    public function view_received_report($date,$petty_id,$type)
    {
        $check_record = $this->db->get_where('petty_cash_college_wise', array('id' => $petty_id))->row();
        $data['user'] = $this->db->get_where('users', array('user_id' => $check_record->assign_to))->row();

        $this->db->select('*');
        $this->db->from('petty_cash_history');
        if ($type == 'received')
            $this->db->where('transaction_pettycash_account = "'.$check_record->id.'" and debit_credit = "D" and created_at >="'.$date.' 00:00:00" and  created_at <="'.$date.'  23:59:59"');
        else
            $this->db->where('transaction_pettycash_account = "'.$check_record->id.'" and debit_credit = "C" and created_at >="'.$date.' 00:00:00" and  created_at <="'.$date.'  23:59:59"');

        $data['debit_credit_data'] = $this->db->get()->result_array();
        $data['selected_date'] = $date;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/pettycash_accounts_debit_credit_closing', $data);
        $this->load->view('inc/footer');
    }

    public function view_expense_report($date,$petty_id,$type)
    {
        @$check_record = $this->db->get_where('petty_cash_college_wise', array('id' => $petty_id))->row();
        @$data['user'] = $this->db->get_where('users', array('user_id' => $check_record->assign_to))->row();

        if ($type == 'expense') {
            $this->db->select('expenses.*,expense_category.*,campuses.*,expense_request.approval_first_by,expense_request.approval_first_comment,expense_request.approval_second_by,expense_request.approval_second_comment,expense_request.created_at as expense_date');
            $this->db->from('expenses');
            $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
            $this->db->join('campuses', 'campuses.campus_id = expenses.campus_id', 'left');
            $this->db->join('expense_request', 'expense_request.id = expenses.request_id', 'left');
            $this->db->where(array('expenses.actual_date >= ' => $date. ' 00:00:00', 'expenses.actual_date <= ' => $date.' 23:59:59','paid_type'=> "cash"));
            if ($petty_id != 0)
                $this->db->where(array('paid_type'=> "cash", 'expenses.add_by_id' => $check_record->assign_to));
            $data['expenses'] = $this->db->get()->result_array();
        }else{
            $this->db->select('*,expense_request.created_at as expense_date');
            $this->db->from('cash_reversal');
            $this->db->join('expenses', 'expenses.expense_id = cash_reversal.expense_id');
            $this->db->join('expense_category', 'expense_category.expense_category_id = expenses.expense_category_id', 'left');
            $this->db->join('campuses', 'campuses.campus_id = expenses.campus_id', 'left');
            $this->db->join('expense_request', 'expense_request.id = expenses.request_id', 'left');
            $this->db->where(array('cash_reversal.created_at >= ' => $date." 00:00:00", 'cash_reversal.created_at <= ' => $date." 23:59:59", 'expenses.add_by_id = ' => $check_record->assign_to));
            $data['expenses'] = $this->db->get()->result_array();
        }

        $total_expense=0;
        foreach($data['expenses'] as $expense)
        {
            $total_expense+=$expense['amount'];
        }

        $data['total_expense'] = $total_expense;

        $data['selected_date'] = $date;
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/accounts_closing_all_expenses', $data);
        $this->load->view('inc/footer');
    }

    public function view_all_expense_report($date,$campus_id,$type)
    {
        $this->db->select('expenses.*,expense_category.*,campuses.*,expense_request.approval_first_by,expense_request.approval_first_comment,expense_request.approval_second_by,expense_request.approval_second_comment,expense_request.created_at as expense_date');
        $this->db->from('expenses');
        $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
        $this->db->join('campuses', 'campuses.campus_id = expenses.campus_id', 'left');
        $this->db->join('expense_request', 'expense_request.id = expenses.request_id', 'left');
        if ($type == 'all') {
            $this->db->where(array('expenses.actual_date >= ' => $date . ' 00:00:00', 'expenses.actual_date <= ' => $date . ' 23:59:59'));
        }else
            $this->db->where(array('expenses.actual_date >= ' => $date . ' 00:00:00', 'expenses.actual_date <= ' => $date . ' 23:59:59', 'expenses.campus_id' => $campus_id));
        $data['expenses'] = $this->db->get()->result_array();

        $total_expense=0;
        foreach($data['expenses'] as $expense)
        {
            $total_expense+=$expense['amount'];
        }

        $data['total_expense'] = $total_expense;

        $data['selected_date'] = $date;
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/accounts_closing_all_expenses', $data);
        $this->load->view('inc/footer');
    }

    public function view_expense_bank_report($date,$petty_id,$type)
    {
        $exp_ids = array();
        $this->db->select('*');
        $this->db->from('bank_reconciliation_statement');
        $this->db->where('account_id = "'.$petty_id.'" and trans_date = "'.$date.'" and debit != "" and (salary_expense_ids IS NOT NULL or expense_id IS NOT NULL)');
        $trans_petty_cash = $this->db->get()->result_array();
        foreach ($trans_petty_cash as $pettey){
            if ($pettey['expense_id'] != NULL){
                array_push($exp_ids,$pettey['expense_id']);
            }else{
                array_push($exp_ids,$pettey['salary_expense_ids']);
            }
        }

        if(count($exp_ids)>0)
        {
            $this->db->select('expenses.*,expense_category.*,campuses.*,expense_request.approval_first_by,expense_request.approval_first_comment,expense_request.approval_second_by,expense_request.approval_second_comment,expense_request.created_at as expense_date');
            $this->db->from('expenses');
            $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
            $this->db->join('campuses', 'campuses.campus_id = expenses.campus_id', 'left');
            $this->db->join('expense_request', 'expense_request.id = expenses.request_id', 'left');
            $this->db->where_in('expenses.expense_id',$exp_ids);
            $data['expenses'] = $this->db->get()->result_array();
        }
        else
        {
            $data['expenses'] = array();
        }

        $data['selected_date'] = $date;
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/accounts_closing_all_expenses', $data);
        $this->load->view('inc/footer');
    }

    public function get_opening_balance($pettycashid, $from_date)
    {
        $check_record = $this->db->get_where('petty_cash_college_wise', array('id' => $pettycashid))->row();

        $data['check_record'] = $check_record;
        $data['openbalance'] = $check_record->opening_balance;

        $this->db->select('sum(amount) as amount');
        $this->db->from('expenses');
        $this->db->where('add_by_id = "' . $check_record->assign_to . '"  and actual_date >= "' . $check_record->given_date . '"  and actual_date < "' . $from_date . '" and paid_type = "cash" and expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)');
        $expenseamount = $this->db->get()->row();

        $this->db->select('sum(cash_reversal.amount) as amount');
        $this->db->from('cash_reversal');
        $this->db->join('expenses', 'expenses.expense_id = cash_reversal.expense_id');
        $this->db->where('expenses.add_by_id = "' . $check_record->assign_to . '"  and cash_reversal.created_at < "' . $from_date . ' 00:00:00"');
        $expensereverseamount = $this->db->get()->row();

        $this->db->select('id as trans_id,"receive from" as detail,"trans" as trans_type,amount_given as amount,"" as expstatus, debit_credit,created_at,"" as image,transaction_by as trans_by ');
        $this->db->from('petty_cash_history');
        $this->db->where('transaction_pettycash_account = "' . $check_record->id . '" and created_at < "' . $from_date . '" ');
        $trans_petty_cash = $this->db->get()->result_array();

        $debit = 0;
        $credit = 0;

        foreach ($trans_petty_cash as $tran) {
            if ($tran['debit_credit'] == 'C') {
                $credit += $tran['amount'];
            } else {
                $debit += $tran['amount'];
            }
        }
        $openbalance = ($data['openbalance'] + $debit + $expensereverseamount->amount) - $credit - $expenseamount->amount;

        return $openbalance;
    }

    public function get_expenses($pettycashid, $date)
    {
        $check_record = $this->db->get_where('petty_cash_college_wise', array('id' => $pettycashid))->row();

        $this->db->select('sum(amount) as amount');
        $this->db->from('expenses');
        $this->db->where('add_by_id = "' . $check_record->assign_to . '" and actual_date >= "' . $date . ' 00:00:00" and actual_date <= "' . $date . ' 23:59:59" 
                            and paid_type = "cash" and expense_id NOT IN (select expense_id from bank_reconciliation_statement where expense_id IS NOT NULL)');
        $expenseamount = $this->db->get()->row();

        return $expenseamount->amount;
    }

    public function get_expenses_reversals($pettycashid, $date)
    {
        $check_record = $this->db->get_where('petty_cash_college_wise', array('id' => $pettycashid))->row();

        $this->db->select('sum(cash_reversal.amount) as amount');
        $this->db->from('cash_reversal');
        $this->db->join('expenses','expenses.expense_id = cash_reversal.expense_id');
        $this->db->where('expenses.add_by_id = "'.$check_record->assign_to.'"  and cash_reversal.created_at >= "'.$date.' 00:00:00"  and cash_reversal.created_at <= "'.$date.' 23:59:59"');
        $expensereverseamount = $this->db->get()->row();

        return $expensereverseamount->amount;
    }

    public function get_received($pettycashid,$date)
    {
        $check_record = $this->db->get_where('petty_cash_college_wise', array('id' => $pettycashid))->row();

        $this->db->select('sum(amount_given) as amount');
        $this->db->from('petty_cash_history');
        $this->db->where('transaction_pettycash_account = "'.$check_record->id.'" and debit_credit = "D" and created_at >="'.$date.' 00:00:00" and  created_at <="'.$date.'  23:59:59"');
        $trans_petty_cash = $this->db->get()->row();

        return $trans_petty_cash->amount;
    }

    public function get_sent($pettycashid,$date)
    {
        $check_record = $this->db->get_where('petty_cash_college_wise', array('id' => $pettycashid))->row();

        $this->db->select('sum(amount_given) as amount');
        $this->db->from('petty_cash_history');
        $this->db->where('transaction_pettycash_account = "'.$check_record->id.'" and debit_credit = "C" and created_at >="'.$date.' 00:00:00" and  created_at <="'.$date.'  23:59:59"');
        $trans_petty_cash = $this->db->get()->row();

        return $trans_petty_cash->amount;
    }

    public function get_accounts_opening_balance($account_id, $from_date)
    {
        $check_record = $this->db->get_where('accounts', array('id' => $account_id))->row();
        $this->db->select('*');
        $this->db->from('transactions_history');
        $this->db->where('transaction_account_id = "'.$account_id.'" and created_at < "'.$from_date.' 00:00:00" ');
        $trans_petty_cash = $this->db->get()->result_array();

        $debit=0;
        $credit=0;
        foreach ($trans_petty_cash as $tran)
        {
            if ($tran['debit_credit']  == 'C' ) {
                $credit+=$tran['amount'];
            }
            else {
                $debit+=$tran['amount'];
            }
        }

        return $debit-$credit;
    }

    public function get_accounts_received($account_id,$date)
    {
        $this->db->select('SUM(amount) as amount');
        $this->db->from('transactions_history');
        $this->db->where('transaction_account_id = "'.$account_id.'" and created_at >="'.$date.' 00:00:00" and created_at <="'.$date.' 23:59:59" and debit_credit = "D"');
        $trans_petty_cash = $this->db->get()->row();
        return $trans_petty_cash->amount;
    }

    public function get_accounts_sent($account_id,$date)
    {
        $this->db->select('SUM(amount) as amount');
        $this->db->from('transactions_history');
        $this->db->where('transaction_account_id = "'.$account_id.'" and created_at >="'.$date.' 00:00:00" and created_at <="'.$date.' 23:59:59" and debit_credit = "C"');
        $trans_petty_cash = $this->db->get()->row();
        return $trans_petty_cash->amount;
    }

    public function view_admissions_report($date,$campus_id)
    {

        $this->db->select('*,classes.name as class_name');
        $this->db->from('payments');
        $this->db->join('students','students.student_id = payments.student_id');
        $this->db->join('classes','classes.class_id = students.class_id');
        $this->db->join('courses','courses.course_id = classes.course_id','left');
        $this->db->join('campuses','campuses.campus_id = classes.campus_id','left');
        $this->db->where('payments.actual_paid_date', $date);
        $this->db->where('payments.paid', '1');
        $this->db->where('classes.campus_id', $campus_id);
        $this->db->group_by('payments.student_id');
        $payments = $this->db->get()->result_array();

        foreach ($payments as $key => $payment) {
            $cos = $this->db->select('*')->get_where("payments", "student_id = '" . $payment['student_id'] . "' and paid = 1")->result_array();
            if (count($cos) > 1){
                unset($payments[$key]);
            }
        }

//        $this->db->select('*,classes.name as class_name');
//        $this->db->from('payments');
//        $this->db->join('students','students.student_id = payments.student_id','left');
//        $this->db->join('classes','classes.class_id = students.class_id','left');
//        $this->db->join('courses','courses.course_id = classes.course_id','left');
//        $this->db->join('campuses','campuses.campus_id = classes.campus_id','left');
//        $this->db->where('payments.actual_paid_date', $date);
//        $this->db->where('payments.paid', '1');
//        $this->db->where('classes.campus_id', $campus_id);
//        $this->db->group_by('payments.student_id');
//        $payments = $this->db->get()->result_array();
//
//        foreach ($payments as $key => $payment) {
//            $cos = $this->db->select('*')->get_where("payments", "student_id = '" . $payment['student_id'] . "' and paid = '1'")->result_array();
//            if (count($cos) > 1){}
//            else
//                unset($payments[$key]);
//        }
        $data['students'] = $payments;


        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/admission_students', $data);
        $this->load->view('inc/footer');
    }

    public function get_bank_accounts_status($account_id,$date)
    {
        $this->db->select('*');
        $this->db->from('bank_reconciliation_statement');
        $this->db->where('account_id = "'.$account_id.'" and trans_date ="'.$date.'"');
        $trans_petty_cash = $this->db->get()->result_array();
        if (count($trans_petty_cash) > 0) {
            $this->db->select('*');
            $this->db->from('bank_reconciliation_statement');
            $this->db->where('account_id = "'.$account_id.'" and trans_date ="'.$date.'" and debit != ""');
            $trans_petty_cash = $this->db->get()->result_array();
            return count($trans_petty_cash);
        }
        else{
            $this->db->select('*');
            $this->db->from('bank_reconciliation_statement');
            $this->db->where('account_id = "'.$account_id.'" and trans_date >"'.$date.'"');
            $trs = $this->db->get()->result_array();
            if (count($trs) > 0)
                return 0;
            else
                return -1;
        }
    }

    public function get_bank_accounts_tagged($account_id,$date)
    {
        $this->db->select('*');
        $this->db->from('bank_reconciliation_statement');
        $this->db->where('account_id = "'.$account_id.'" and trans_date ="'.$date.'" and debit != ""');
        $trans_petty_cash = $this->db->get()->result_array();
        $un_count = 0;
        $counted = 0;

        foreach ($trans_petty_cash as $key=>$statement)
        {
            if ($statement['payment_id'] ==  NULL && $statement['related_to'] ==  0 && $statement['bank_transfer_id'] ==  NULL &&
                $statement['expense_id'] ==  NULL && $statement['statement_id'] ==  NULL  && $statement['closing_id'] ==  NULL &&
                $statement['is_council_fee'] ==  NULL && $statement['paypro_id'] ==  NULL && $statement['salary_expense_ids'] ==  NULL )
            {
                $un_count++;
            }else
                $counted++;
        }

        return array("counted"=>$counted,"uncounted"=>$un_count);
    }

    public function get_bank_account_expense($account_id,$date)
    {
        $amount = 0;
        $this->db->select('debit');
        $this->db->from('bank_reconciliation_statement');
        $this->db->where('account_id = "'.$account_id.'" and trans_date ="'.$date.'" and debit != "" and (salary_expense_ids IS NOT NULL or expense_id IS NOT NULL)');
        $trans_petty_cash = $this->db->get()->result_array();
        foreach ($trans_petty_cash as $key=>$statement)
        {
            $amount+=(int)str_replace(",","",$statement['debit']);
        }

        return $amount;
    }

    public function get_campus_status($campus_id,$date)
    {
        $found = 0;
        $not_found = 0;
        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('machine_data','machine_data.teacher_student_id = users.user_id and machine_data.type = "teacher"');
        $this->db->where('users.status',"1");
        $this->db->where('users.campus_id',$campus_id);
        $sebs = $this->db->get()->result_array();

        foreach ($sebs as $seb) {
            $this->db->select('*');
            $this->db->from('attendence');
            $this->db->where('machine_user_id',$seb['machine_id']);
            $this->db->where("time >= '$date 00:00:00' and time <= '$date 23:59:59'");
            $ses = $this->db->get()->result_array();
            if (count($ses) > 0)
                $found++;
            else
                $not_found++;
        }
        return array("counted"=>$found,"uncounted"=>$not_found);
    }

    public function view_attendance($date,$campus_id,$type)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('machine_data','machine_data.teacher_student_id = users.user_id and machine_data.type = "teacher"');
        $this->db->join('campuses','campuses.campus_id = users.campus_id');
        $this->db->where('users.status',"1");
        $this->db->where('users.campus_id',$campus_id);
        $sebs = $this->db->get()->result_array();

        foreach ($sebs as $key=>$seb) {
            $this->db->select('*');
            $this->db->from('attendence');
            $this->db->where('machine_user_id',$seb['machine_id']);
            $this->db->where("time >= '$date 00:00:00' and time <= '$date 23:59:59'");
            $this->db->order_by("id","ASC");
            $ses = $this->db->get()->result_array();
            if ($type == 'present') {
                if (count($ses) > 0)
                    $sebs[$key]['in_time'] = $ses[0]['time'] ;
                else
                    unset($sebs[$key]);
            }else{
                if (count($ses) > 0)
                    unset($sebs[$key]);
                else
                    $sebs[$key]['in_time'] = '';
            }
        }

        $data['students'] = $sebs;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/view_attendance', $data);
        $this->load->view('inc/footer');
    }

    public function CooCashReport()
    {
        $access = checkUserAccess();
        if (@$this->input->post("to_date"))
            $date = $this->input->post("to_date");
        else
            $date = date("Y-m-d");

        $this->db->select('*');
        $this->db->from('petty_cash_college_wise');
        $this->db->join('campuses', 'campuses.campus_id = petty_cash_college_wise.campus_id', 'left');
        $this->db->join('users', 'users.user_id = petty_cash_college_wise.assign_to', 'left');
        $this->db->join('designations', 'designations.designation_id = users.designation_id', 'left');
        $this->db->where('petty_cash_college_wise.petty_status', "1");
        $this->db->where_in('petty_cash_college_wise.id', explode(",",$access[0]['petty_cash_users']));
        $data['Pettycashs'] = $this->db->get()->result_array();

        foreach ($data['Pettycashs'] as $index => $petts) {
            $data['Pettycashs'][$index]['opening_balance'] = $this->get_opening_balance($petts ['id'], $date);
            $data['Pettycashs'][$index]['remaining_balance'] = pettycash_statement($petts ['id']);
            $data['Pettycashs'][$index]['expenses'] = $this->get_expenses($petts ['id'], $date);
            $data['Pettycashs'][$index]['reversal'] = $this->get_expenses_reversals($petts ['id'], $date);
            $data['Pettycashs'][$index]['received'] = $this->get_received($petts ['id'], $date);
            $data['Pettycashs'][$index]['sent'] = $this->get_sent($petts ['id'], $date);
        }
        $data['selected_date'] = $date;
        $today = $date;

        // Get Campus Closings
        $this->db->select('*,closing_persons.campus_id as campus_id');
        $this->db->from('closing_persons');
        $this->db->join('campuses','campuses.campus_id = closing_persons.campus_id','left');
        $this->db->join('users','users.user_id = closing_persons.user_id','left');
        $this->db->where('closing_persons.active_status = "1"');
        $dataclose = $this->db->get()->result_array();

        $sq = 'select closing_perday.campus_id,campus_name,
                (select for_day from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
		        (select for_month from closing_perday where campus_id = campuses.campus_id order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,
		        MAX(for_year) as year from closing_perday 
		        left join campuses on campuses.campus_id = closing_perday.campus_id 
		        where (select count(*) from closing_persons where closing_persons.campus_id = closing_perday.campus_id and closing_persons.active_status = 1) > 0
		        GROUP by closing_perday.campus_id';
        $data['campusclosings'] = $this->db->query($sq)->result_array();

        $sq = 'select closing_perday.campus_id,campus_name,
		(select for_day   from closing_perday where campus_id = campuses.campus_id and checked_by = "1" order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as day,
		(select for_month from closing_perday where campus_id = campuses.campus_id and checked_by = "1" order by closing_perday.for_year desc, closing_perday.for_month desc, closing_perday.for_day desc LIMIT 1) as month,
		MAX(for_year) as year from closing_perday 
		left join campuses on campuses.campus_id = closing_perday.campus_id
		where (select count(*) from closing_persons where closing_persons.campus_id = closing_perday.campus_id and closing_persons.active_status = 1) > 0
        GROUP by closing_perday.campus_id';
        $data['campusclosingverified'] = $this->db->query($sq)->result_array();
        $data['campusclosings'] = array_values($data['campusclosings']);

        foreach ($dataclose as $key=>$closing) {
            $sq = "select * from closing_perday where campus_id = '".$closing['campus_id']."' and for_month = '".date('m', strtotime($today))."' and for_day = '".date('d', strtotime($today))."'and for_year = '".date('Y', strtotime($today))."'";
            $closed = $this->db->query($sq)->result_array();

            if (count($closed)>0) {
                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('closing_id = "'.$closed[0]['campus_closing_id'].'"');
                $query = $this->db->get()->result_array();

                $value = array_sum(array_column($query,'actual_amount'));

                $dataclose[$key]['closing_amount'] = $value;
                $dataclose[$key]['closed_status'] = '1';
                $dataclose[$key]['closing_id'] = $closed[0]['id'];
                $dataclose[$key]['transaction_no'] = $closed[0]['transaction_no'];
                $dataclose[$key]['close_type'] = $closed[0]['close_type'];
                $dataclose[$key]['closed_by'] = $closed[0]['closed_by'];
                $dataclose[$key]['checked_by'] = $closed[0]['checked_by'];
                $dataclose[$key]['account_id'] = $closed[0]['account_id'];
                $dataclose[$key]['partialy_closed_image'] = $closed[0]['partialy_closed_image'];
            }
            else {
                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                $this->db->where('fee_pay_through = "college"');
                $this->db->where('actual_paid_date = "'.$today.'"');
                $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
                $query = $this->db->get()->result_array();

                $this->db->select('*');
                $this->db->from('payments');
                $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                $this->db->where('merged_challan is null');
                $this->db->where('fee_pay_through = "college"');
                $this->db->where('actual_paid_date = "'.$today.'"');
                $this->db->where('payments.paid = 1');
                $query2 = $this->db->get()->result_array();

                $yesterday = date('Y-m-d', strtotime($today. ' - 1 days'));

                $sq = "select * from closing_perday where campus_id = '".$closing['campus_id']."' and for_month = '".date('m', strtotime($yesterday))."' and for_day = '".date('d', strtotime($yesterday))."'and for_year = '".date('Y', strtotime($yesterday))."'";
                $closed = $this->db->query($sq)->result_array();

                if (count($closed)>0)  {

                    $this->db->select('*');
                    $this->db->from('payments');
                    $this->db->join('students','students.student_id = payments.student_id','left');
                    $this->db->join('courses','courses.course_id=students.course_id','inner');
                    $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                    $this->db->where('merged_challan IS NOT NULL and actual_amount > 0');
                    $this->db->where('fee_pay_through = "college"');
                    $this->db->where('actual_paid_date = "'.$yesterday.'"');
                    $this->db->where('closing_id IS NULL');
                    $this->db->group_by("CASE WHEN merged_challan IS NOT NULL THEN merged_challan else '' end",false);
                    $query3 = $this->db->get()->result_array();

                    $this->db->select('*');
                    $this->db->from('payments');
                    $this->db->join('students','students.student_id = payments.student_id','left');
                    $this->db->join('courses','courses.course_id=students.course_id','inner');
                    $this->db->where('submitted_fee_campus_id', $closing['campus_id']);
                    $this->db->where('merged_challan is null');
                    $this->db->where('fee_pay_through = "college"');
                    $this->db->where('actual_paid_date = "'.$yesterday.'"');
                    $this->db->where('closing_id IS NULL');
                    $this->db->where('payments.paid = 1');
                    $query4 = $this->db->get()->result_array();
                    $final = array_merge($query3, $query4,$query, $query2);
                }

                else {
                    $final = array_merge($query, $query2);
                }

                $value = array_sum(array_column($final,'actual_amount'));

                $this->db->select('sum(asset_sales.sale_amount) as total');
                $this->db->from('asset_sales');
                $this->db->join('products','products.product_id = asset_sales.product_id','inner');
                $this->db->where("asset_sales.sold_date >= '$today 00:00:00' and asset_sales.sold_date <= '$today 23:59:59' and products.campus_id = '".$closing['campus_id']."'");
                $asset_sales_sum_today = $this->db->get()->result_array();

                $this->db->select('sum(asset_sales.sale_amount) as total');
                $this->db->from('asset_sales');
                $this->db->join('products','products.product_id = asset_sales.product_id','inner');
                $this->db->where("asset_sales.sold_date >= '$yesterday 00:00:00' and asset_sales.sold_date <= '$yesterday 23:59:59' and products.campus_id = '".$closing['campus_id']."' and closing_id IS NULL");
                $asset_sales_sum_yesterday = $this->db->get()->result_array();

                $asset_sale_amount = $asset_sales_sum_today[0]['total'] + $asset_sales_sum_yesterday[0]['total'];

                $this->db->select('sum(sales_payments.payment_amount) as total');
                $this->db->from('sales');
                $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
                $this->db->join('people','people.person_id  = sales.customer_id','inner');
                $this->db->where("sales.sale_time >= '$today 00:00:00' and sales.sale_time <= '$today 23:59:59' and sales.campus_id = '".$closing['campus_id']."'");
                $sales_sum = $this->db->get()->result_array();

                $this->db->select('sum(sales_payments.payment_amount) as total');
                $this->db->from('sales');
                $this->db->join('sales_payments','sales_payments.sale_id=sales.sale_id','left');
                $this->db->join('people','people.person_id  = sales.customer_id','inner');
                $this->db->where("sales.sale_time >= '$yesterday 00:00:00' and sales.sale_time <= '$yesterday 23:59:59' and sales.campus_id = '".$closing['campus_id']."'");
                $sales_sum_yesterday = $this->db->get()->result_array();

                $sale_amount = $sales_sum[0]['total'] + $sales_sum_yesterday[0]['total'];

                $dataclose[$key]['closing_amount'] = $value+$sale_amount+$asset_sale_amount;
                $dataclose[$key]['closed_status'] = '0';
                $dataclose[$key]['closing_id'] = '';
                $dataclose[$key]['close_type'] = '0';
                $dataclose[$key]['transaction_no'] = '';
                $dataclose[$key]['closed_by'] = '';
                $dataclose[$key]['checked_by'] = '';
                $dataclose[$key]['account_id'] = '';
                $dataclose[$key]['partialy_closed_image'] = @$closed[0]['partialy_closed_image'];
            }
        }
        $data['closings']=$dataclose;
        ///////////   End Closing /////////////

        $data['last_closing'] = $this->db->order_by('id','DESC')->get("accounts_daily_closing")->row()->closing_date;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/pettycash_coo_closing', $data);
        $this->load->view('inc/footer');
    }

    public function print_expense_report($date,$petty_id,$type)
    {
        @$check_record = $this->db->get_where('petty_cash_college_wise', array('id' => $petty_id))->row();
        @$data['user'] = $this->db->get_where('users', array('user_id' => $check_record->assign_to))->row();

        if ($type == 'expense') {
            $this->db->select('expenses.*,expense_category.*,campuses.*,expense_request.approval_first_by,expense_request.approval_first_comment,expense_request.approval_second_by,expense_request.approval_second_comment,expense_request.created_at as expense_date');
            $this->db->from('expenses');
            $this->db->join('expense_category', 'expense_category.expense_category_id=expenses.expense_category_id', 'left');
            $this->db->join('campuses', 'campuses.campus_id = expenses.campus_id', 'left');
            $this->db->join('expense_request', 'expense_request.id = expenses.request_id', 'left');
            $this->db->where(array('expenses.actual_date >= ' => $date. ' 00:00:00', 'expenses.actual_date <= ' => $date.' 23:59:59'));
            if ($petty_id != 0)
                $this->db->where(array('expenses.add_by_id = ' => $check_record->assign_to));
            $data['expenses'] = $this->db->get()->result_array();
        }else{
            $this->db->select('*,expense_request.created_at as expense_date');
            $this->db->from('cash_reversal');
            $this->db->join('expenses', 'expenses.expense_id = cash_reversal.expense_id');
            $this->db->join('expense_category', 'expense_category.expense_category_id = expenses.expense_category_id', 'left');
            $this->db->join('campuses', 'campuses.campus_id = expenses.campus_id', 'left');
            $this->db->join('expense_request', 'expense_request.id = expenses.request_id', 'left');
            $this->db->where(array('cash_reversal.created_at >= ' => $date." 00:00:00", 'cash_reversal.created_at <= ' => $date." 23:59:59", 'expenses.add_by_id = ' => $check_record->assign_to));
            $data['expenses'] = $this->db->get()->result_array();
        }

        $data['selected_date'] = $date;
        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/print_closing_all_expenses', $data);
        $this->load->view('inc/footer');
    }

    public function agent_view_statement_coo()
    {
        $from_date = @$this->input->post('from_date');
        $end_date = @$this->input->post('to_date');
        $account_id = $this->input->post('account_id');

        $this->db->select('*,bank_reconciliation_statement.id as trans_id,bank_reconciliation_statement.statement_id as str_id,bank_reconciliation_statement.closing_id as closing_bank_id');
        $this->db->from('bank_reconciliation_statement');
        $this->db->join('payments','payments.statement_id = bank_reconciliation_statement.id','left');
        $this->db->join('accounts','accounts.id=bank_reconciliation_statement.account_id','left');
        $this->db->where("bank_reconciliation_statement.trans_date >= '$from_date' and bank_reconciliation_statement.trans_date <= '$end_date' and bank_reconciliation_statement.account_id = '".$account_id."'");
        $this->db->group_by("bank_reconciliation_statement.description,bank_reconciliation_statement.trans_date,bank_reconciliation_statement.credit,bank_reconciliation_statement.debit")
            ->order_by('bank_reconciliation_statement.trans_date','ASC');
        $data['entries']=$this->db->get()->result_array();
        $data['accounts'] = $this->db->query('SELECT * FROM `accounts` WHERE `type` = "1"')->result_array();
        $data['from_date'] = $from_date;
        $data['to_date'] = $end_date;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/agent_bank_statement_coo',$data);
        $this->load->view('inc/footer');
    }
    
    public function agent_view_statement()
    {
        $from_date = @$this->input->post('from_date');
        $amount = @$this->input->post('amount');
        $account_id = $this->input->post('account_id');

        $this->db->select('*,bank_reconciliation_statement.id as trans_id,bank_reconciliation_statement.statement_id as str_id,bank_reconciliation_statement.closing_id as closing_bank_id');
        $this->db->from('bank_reconciliation_statement');
        $this->db->join('payments','payments.statement_id = bank_reconciliation_statement.id','left');
        $this->db->join('accounts','accounts.id=bank_reconciliation_statement.account_id','left');
        $this->db->where("bank_reconciliation_statement.trans_date = '$from_date' AND CAST(REPLACE(bank_reconciliation_statement.credit, ',', '') AS DECIMAL(15,2)) = ".(float)$amount." and bank_reconciliation_statement.account_id = '".$account_id."'");
        $this->db->group_by("bank_reconciliation_statement.description,bank_reconciliation_statement.trans_date,bank_reconciliation_statement.credit,bank_reconciliation_statement.debit")
            ->order_by('bank_reconciliation_statement.trans_date','ASC');
        $data['entries']=$this->db->get()->result_array();
        $data['accounts'] = $this->db->query('SELECT * FROM `accounts` WHERE `type` = "1"')->result_array();
        $data['from_date'] = $from_date;
        $data['amount'] = $amount;

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/agent_bank_statement',$data);
        $this->load->view('inc/footer');
    }

    public function students_backup_report()
    {
        $data['classes'] = $this->clas->getAllClassesActiveInactive();

        if($this->input->post('backup_date'))
        {
            $data['backup_date']=$this->input->post('backup_date');
        }
        else
        {
            $data['backup_date']=date('Y-m-d');
        }

        $this->load->view('inc/header');
        $this->load->view('inc/sidebar');
        $this->load->view('reports/students_backup_report',$data);
        $this->load->view('inc/footer');
    }
}







